<?php

namespace App\Http\Controllers;

use App\Models\PgpVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function show(Request $request)
    {
        return view('profile.show', ['user' => $request->user()]);
    }

    /**
     * Show the profile completion form for new users
     */
    public function complete(Request $request)
    {
        $user = $request->user();

        // Check if security settings are already completed
        if ($this->hasCompletedSecuritySetup($user)) {
            return redirect()->route('home')
                ->with('info', 'Security settings already completed.');
        }

        return view('profile.complete');
    }

    /**
     * Update general profile information
     * REMOVED: public_username editing - security reasons
     * PGP key updates now require verification flow
     */
    public function update(Request $request)
    {
        // This method is deprecated - PGP updates go through verification flow
        return redirect()->route('profile.show')
            ->with('info', 'Please use the PGP verification process to update your public key.');
    }

    /**
     * Show password change form
     */
    public function showPasswordForm()
    {
        return view('profile.password');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ]);

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return back()->with('success', 'Password updated successfully!');
    }

    /**
     * Show PIN change form
     */
    public function showPinForm()
    {
        return view('profile.pin');
    }

    /**
     * Update user PIN
     */
    public function updatePin(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_pin' => 'required|digits:6',
            'pin' => 'required|digits:6|confirmed',
        ]);

        // Verify current PIN
        if (!Hash::check($validated['current_pin'], $user->pin)) {
            return back()->withErrors(['current_pin' => 'Current PIN is incorrect.']);
        }

        $user->update([
            'pin' => Hash::make($validated['pin'])
        ]);

        return back()->with('success', 'PIN updated successfully!');
    }

    /**
     * Show recovery passphrases form
     */
    public function showPassphrasesForm()
    {
        return view('profile.passphrases');
    }

    /**
     * Update recovery passphrases
     */
    public function updatePassphrases(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_passphrase_1' => 'required|string',
            'passphrase_1' => 'required|string|min:5|max:64',
            'passphrase_2' => 'nullable|string|min:5|max:64',
        ]);

        // Verify current primary passphrase
        if (!Hash::check($validated['current_passphrase_1'], $user->passphrase_1)) {
            return back()->withErrors(['current_passphrase_1' => 'Current passphrase is incorrect.']);
        }

        $user->update([
            'passphrase_1' => Hash::make($validated['passphrase_1']),
            'passphrase_2' => $validated['passphrase_2'] ? Hash::make($validated['passphrase_2']) : null,
        ]);

        return back()->with('success', 'Recovery passphrases updated successfully!');
    }

    /**
     * Check if user has completed initial security setup
     */
    private function hasCompletedSecuritySetup(User $user): bool
    {
        // Check if still using temporary/default values
        $hasRealPin = !Hash::check('000000', $user->pin);
        $hasRealPassphrase = !str_starts_with($user->passphrase_1, '$2y$') ||
            !str_contains($user->passphrase_1, 'temporary_passphrase_');

        return $hasRealPin && $hasRealPassphrase;
    }

    /**
     * Display public profile view with forum statistics
     */
    public function showPublicView($username)
    {
        $user = User::where('username_pub', $username)->firstOrFail();

        // Forum statistics
        $forumStats = [
            'posts_count' => $user->forumPosts()->count(),
            'comments_count' => $user->forumComments()->count(),
            'recent_posts' => $user->forumPosts()->latest()->take(5)->get(),
            'recent_comments' => $user->forumComments()->with('post')->latest()->take(5)->get(),
        ];

        return view('profile.show_public', compact('user', 'forumStats'));
    }

    /**
     * Update security settings (PIN and passphrases) for new users
     */
    public function updateSecurity(Request $request)
    {
        $user = auth()->user();

        // Check if security settings are already completed
        if ($this->hasCompletedSecuritySetup($user)) {
            return redirect()->route('home')
                ->with('info', 'Security settings already completed.');
        }

        $validated = $request->validate([
            'pin' => 'required|digits:6|confirmed',
            'passphrase_1' => 'required|string|min:5|max:64',
            'passphrase_2' => 'nullable|string|min:5|max:64',
        ]);

        $user->update([
            'pin' => Hash::make($validated['pin']),
            'passphrase_1' => Hash::make($validated['passphrase_1']),
            'passphrase_2' => $validated['passphrase_2'] ? Hash::make($validated['passphrase_2']) : null,
        ]);

        return redirect()->route('home')
            ->with('success', 'Security setup completed successfully! Your account is now fully secured.');
    }

    /**
     * Show PGP key setup/update form
     */
    public function showPgpForm()
    {
        return view('profile.pgp', [
            'user' => auth()->user(),
            'hasExistingKey' => !empty(auth()->user()->pgp_pub_key),
        ]);
    }

    /**
     * Initiate PGP verification - Generate encrypted challenge
     */
    public function initiatePgpVerification(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'pgp_pub_key' => 'required|string|min:100|max:10000',
        ]);

        $pgpKey = trim($validated['pgp_pub_key']);

        // Basic PGP key format validation
        if (!str_contains($pgpKey, '-----BEGIN PGP PUBLIC KEY BLOCK-----') ||
            !str_contains($pgpKey, '-----END PGP PUBLIC KEY BLOCK-----')) {
            return back()->withErrors([
                'pgp_pub_key' => 'Invalid PGP public key format. Must contain BEGIN and END markers.'
            ])->withInput();
        }

        // Cancel any existing pending verifications for this user
        PgpVerification::where('user_id', $user->id)
            ->where('status', 'pending')
            ->update(['status' => 'expired']);

        // Generate a random verification code
        $verificationCode = Str::upper(Str::random(12)); // e.g., "A3F9K2L8P5M1"

        // Create the challenge message
        $challengeMessage = strtoupper(config('app.name')) . " PGP VERIFICATION\n\n"
            . "Username: {$user->username_pub}\n"
            . "Verification Code: {$verificationCode}\n"
            . "Timestamp: " . now()->toDateTimeString() . "\n\n"
            . "Please decrypt this message with your private key and submit the verification code above.\n"
            . "This code expires in 1 hour.";

        // Encrypt the message with the provided PGP public key
        try {
            $encryptedMessage = $this->encryptWithPgp($challengeMessage, $pgpKey);
        } catch (\Exception $e) {
            return back()->withErrors([
                'pgp_pub_key' => 'Failed to encrypt with provided key. Please verify it is a valid PGP public key.'
            ])->withInput();
        }

        // Store the verification challenge
        $verification = PgpVerification::create([
            'user_id' => $user->id,
            'pgp_pub_key' => $pgpKey,
            'verification_code' => $verificationCode,
            'encrypted_message' => $encryptedMessage,
            'status' => 'pending',
            'expires_at' => now()->addHour(),
            'attempts' => 0,
        ]);

        return redirect()->route('profile.pgp.verify', $verification->id)
            ->with('success', 'Verification challenge generated. Decrypt the message and submit the code.');
    }

    /**
     * Show PGP verification challenge page
     */
    public function showPgpVerificationChallenge(PgpVerification $verification)
    {
        $user = auth()->user();

        // Ensure user owns this verification
        if ($verification->user_id !== $user->id) {
            abort(403, 'Unauthorized access to verification.');
        }

        // Check if expired or invalid
        if (!$verification->canAttempt()) {
            return redirect()->route('profile.pgp')
                ->with('error', 'This verification has expired or reached maximum attempts. Please start over.');
        }

        return view('profile.pgp-verify', compact('verification'));
    }

    /**
     * Verify the decrypted code submitted by user
     */
    public function verifyPgpCode(Request $request, PgpVerification $verification)
    {
        $user = $request->user();

        // Ensure user owns this verification
        if ($verification->user_id !== $user->id) {
            abort(403, 'Unauthorized access to verification.');
        }

        // Check if can still attempt
        if (!$verification->canAttempt()) {
            return redirect()->route('profile.pgp')
                ->with('error', 'This verification has expired or reached maximum attempts. Please start over.');
        }

        $validated = $request->validate([
            'verification_code' => 'required|string',
        ]);

        $submittedCode = strtoupper(trim($validated['verification_code']));

        // Verify the code matches
        if ($submittedCode === $verification->verification_code) {
            // Success! Mark as verified
            $verification->markAsVerified();

            // Update user's PGP key
            $user->update([
                'pgp_pub_key' => $verification->pgp_pub_key,
            ]);

            return redirect()->route('profile.show')
                ->with('success', 'PGP public key verified and saved successfully!');
        } else {
            // Failed attempt
            $verification->incrementAttempts();
            $attemptsLeft = 5 - $verification->fresh()->attempts;

            return back()->withErrors([
                'verification_code' => "Incorrect verification code. You have {$attemptsLeft} attempts remaining."
            ]);
        }
    }

    /**
     * Encrypt a message using PGP public key with gnupg extension
     *
     * @param string $message The plaintext message to encrypt
     * @param string $publicKey The PGP public key block
     * @return string The encrypted PGP message
     * @throws \Exception If encryption fails or gnupg is not available
     */
    private function encryptWithPgp(string $message, string $publicKey): string
    {
        // Ensure gnupg extension is loaded
        if (!extension_loaded('gnupg')) {
            throw new \Exception(
                'PGP encryption requires the php-gnupg extension. ' .
                'Install with: sudo apt install php-gnupg or sudo pecl install gnupg'
            );
        }

        // Create a temporary GPG home directory in /tmp
        $tempGpgHome = '/tmp/teardrop_gpg_' . uniqid('', true);

        try {
            // Create the temporary directory
            if (!mkdir($tempGpgHome, 0700, true)) {
                throw new \Exception('Failed to create temporary GPG directory');
            }

            // Set environment variable for GPG home
            putenv("GNUPGHOME={$tempGpgHome}");

            // Normalize the public key (trim whitespace, ensure proper line endings)
            $publicKey = trim($publicKey);
            $publicKey = str_replace("\r\n", "\n", $publicKey);
            $publicKey = str_replace("\r", "\n", $publicKey);

            // Validate basic PGP key structure
            if (!preg_match('/-----BEGIN PGP PUBLIC KEY BLOCK-----.*-----END PGP PUBLIC KEY BLOCK-----/s', $publicKey)) {
                throw new \Exception('Invalid PGP key format. Key must contain BEGIN and END markers.');
            }

            // Initialize gnupg
            $gpg = new \gnupg();

            // Set error mode to throw exceptions
            $gpg->seterrormode(\gnupg::ERROR_EXCEPTION);

            // Set armor mode for ASCII output
            $gpg->setarmor(1);

            // Import the public key
            $importResult = $gpg->import($publicKey);

            // Validate import result - gnupg returns array with fingerprint on success
            if (!$importResult) {
                throw new \Exception('Key import failed. The key may be corrupted or invalid.');
            }

            // Handle both array and object returns
            $fingerprint = null;
            if (is_array($importResult)) {
                $fingerprint = $importResult['fingerprint'] ?? null;
            } elseif (is_object($importResult)) {
                $fingerprint = $importResult->fingerprint ?? null;
            }

            if (empty($fingerprint)) {
                // Try alternate method - check if key was imported
                $allKeys = $gpg->keyinfo("");
                if (!empty($allKeys)) {
                    // Get the most recently imported key
                    $lastKey = end($allKeys);
                    $fingerprint = $lastKey['subkeys'][0]['fingerprint'] ?? null;
                }
            }

            if (empty($fingerprint)) {
                throw new \Exception('Failed to extract fingerprint from imported key. Key may be malformed.');
            }

            // Verify key was imported successfully
            $keyInfo = $gpg->keyinfo($fingerprint);
            if (empty($keyInfo)) {
                throw new \Exception('Imported key could not be verified in keyring.');
            }

            // Check if key can be used for encryption
            $canEncrypt = false;
            foreach ($keyInfo as $key) {
                // Check main key capabilities
                if (isset($key['can_encrypt']) && $key['can_encrypt']) {
                    $canEncrypt = true;
                    break;
                }
                // Also check subkeys
                if (isset($key['subkeys'])) {
                    foreach ($key['subkeys'] as $subkey) {
                        if (isset($subkey['can_encrypt']) && $subkey['can_encrypt']) {
                            $canEncrypt = true;
                            break 2;
                        }
                    }
                }
            }

            if (!$canEncrypt) {
                throw new \Exception('This key does not have encryption capabilities. Please use a key that supports encryption.');
            }

            // Add the key for encryption
            if (!$gpg->addencryptkey($fingerprint)) {
                throw new \Exception('Failed to add encryption key to GPG context.');
            }

            // Encrypt the message
            $encrypted = $gpg->encrypt($message);

            if (!$encrypted || empty($encrypted)) {
                throw new \Exception('Encryption produced empty result.');
            }

            // Verify encrypted message has proper PGP format
            if (!str_contains($encrypted, '-----BEGIN PGP MESSAGE-----')) {
                throw new \Exception('Encryption did not produce valid PGP message format');
            }

            return $encrypted;

        } catch (\Exception $e) {
            // Log the error for debugging with more context
            \Log::error('PGP encryption failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'key_length' => strlen($publicKey ?? ''),
                'key_preview' => substr($publicKey ?? '', 0, 100),
            ]);

            // Throw user-friendly error
            throw new \Exception(
                'Failed to encrypt with PGP key: ' . $e->getMessage() .
                '. Please verify your public key is valid and can be used for encryption.'
            );
        } finally {
            // Cleanup: Remove temporary GPG home directory
            if (isset($tempGpgHome) && is_dir($tempGpgHome)) {
                // Remove all files in the temp directory
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($tempGpgHome, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );

                foreach ($files as $fileinfo) {
                    $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                    @$todo($fileinfo->getRealPath());
                }

                @rmdir($tempGpgHome);
            }

            // Reset GPG home to default
            putenv("GNUPGHOME");
        }
    }

    /**
     * Show account deletion confirmation form
     */
    public function showDeleteAccountForm()
    {
        $user = auth()->user();

        // User must have a PGP key configured to delete account
        if (empty($user->pgp_pub_key)) {
            return redirect()->route('profile.show')
                ->with('error', 'You must configure a PGP key before you can delete your account.');
        }

        return view('profile.delete-account');
    }

    /**
     * Process account deletion - requires PGP verification + password
     */
    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        // User must have a PGP key configured
        if (empty($user->pgp_pub_key)) {
            return redirect()->route('profile.show')
                ->with('error', 'PGP key configuration required.');
        }

        // Validate inputs
        $validated = $request->validate([
            'password' => 'required|string',
            'confirmation_text' => 'required|string|in:DELETE MY ACCOUNT',
        ], [
            'confirmation_text.in' => 'You must type "DELETE MY ACCOUNT" exactly to confirm.',
        ]);

        // Verify password
        if (!Hash::check($validated['password'], $user->password)) {
            return back()->withErrors([
                'password' => 'Password is incorrect.'
            ]);
        }

        // Prepare PGP key (trim and normalize like in initiatePgpVerification)
        $pgpKey = trim($user->pgp_pub_key);

        // Basic PGP key format validation
        if (!str_contains($pgpKey, '-----BEGIN PGP PUBLIC KEY BLOCK-----') ||
            !str_contains($pgpKey, '-----END PGP PUBLIC KEY BLOCK-----')) {
            return back()->withErrors([
                'password' => 'Your stored PGP key appears to be invalid. Please update your PGP key.'
            ]);
        }

        // Generate PGP challenge for final verification
        $verificationCode = Str::upper(Str::random(16));

        // Create the deletion challenge message
        $challengeMessage = strtoupper(config('app.name')) . " ACCOUNT DELETION VERIFICATION\n\n"
            . "WARNING: This action is PERMANENT and IRREVERSIBLE!\n\n"
            . "Username: {$user->username_pub}\n"
            . "Verification Code: {$verificationCode}\n"
            . "Timestamp: " . now()->toDateTimeString() . "\n\n"
            . "If you wish to proceed with account deletion, decrypt this message\n"
            . "and submit the verification code above within 30 minutes.\n\n"
            . "Once confirmed, your account and all associated data will be permanently deleted.";

        // Encrypt with user's PGP key
        try {
            $encryptedMessage = $this->encryptWithPgp($challengeMessage, $pgpKey);
        } catch (\Exception $e) {
            \Log::error('PGP encryption failed for account deletion', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors([
                'password' => 'Failed to generate PGP challenge. Please verify your PGP key is valid.'
            ]);
        }

        // Store deletion challenge in session (30 minute expiry)
        session([
            'account_deletion_challenge' => [
                'code' => $verificationCode,
                'encrypted_message' => $encryptedMessage,
                'user_id' => $user->id,
                'expires_at' => now()->addMinutes(30)->timestamp,
                'attempts' => 0,
            ]
        ]);

        return view('profile.delete-account-verify', [
            'encryptedMessage' => $encryptedMessage,
        ]);
    }

    /**
     * Verify PGP code and permanently delete account
     */
    public function confirmDeleteAccount(Request $request)
    {
        $user = auth()->user();

        // Get challenge from session
        $challenge = session('account_deletion_challenge');

        if (!$challenge) {
            return redirect()->route('profile.show')
                ->with('error', 'No active deletion challenge found. Please start over.');
        }

        // Verify user ID matches
        if ($challenge['user_id'] !== $user->id) {
            session()->forget('account_deletion_challenge');
            abort(403, 'Invalid deletion challenge.');
        }

        // Check if expired (30 minutes)
        if ($challenge['expires_at'] < now()->timestamp) {
            session()->forget('account_deletion_challenge');
            return redirect()->route('profile.show')
                ->with('error', 'Deletion challenge has expired. Please start over.');
        }

        // Check max attempts (5)
        if ($challenge['attempts'] >= 5) {
            session()->forget('account_deletion_challenge');
            return redirect()->route('profile.show')
                ->with('error', 'Maximum verification attempts exceeded. Please start over.');
        }

        // Validate submitted code
        $validated = $request->validate([
            'verification_code' => 'required|string',
        ]);

        $submittedCode = strtoupper(trim($validated['verification_code']));

        // Check if code matches
        if ($submittedCode !== $challenge['code']) {
            // Increment attempts
            $challenge['attempts']++;
            session(['account_deletion_challenge' => $challenge]);

            $attemptsLeft = 5 - $challenge['attempts'];
            return back()->withErrors([
                'verification_code' => "Incorrect verification code. You have {$attemptsLeft} attempts remaining."
            ]);
        }

        // CODE VERIFIED - Proceed with account deletion
        // Clear the session challenge
        session()->forget('account_deletion_challenge');

        // Log the deletion for audit purposes
        \Log::info('Account deletion confirmed', [
            'user_id' => $user->id,
            'username_pub' => $user->username_pub,
            'username_pri' => $user->username_pri,
            'deleted_at' => now()->toDateTimeString(),
        ]);

        // Store username for goodbye message
        $username = $user->username_pub;

        // Logout the user
        auth()->logout();

        // Permanently delete the user and all related data
        // Laravel's cascade deletes will handle related records if set up in migrations
        $user->delete();

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to homepage with goodbye message
        return redirect()->route('home')
            ->with('success', "Account '{$username}' has been permanently deleted. Goodbye.");
    }
}
