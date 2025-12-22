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
        $challengeMessage = "TEARDROP PGP VERIFICATION\n\n"
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
     * Encrypt a message using PGP public key
     * This is a placeholder - in production you would use gnupg extension or external service
     */
    private function encryptWithPgp(string $message, string $publicKey): string
    {
        // IMPORTANT: This is a MOCK implementation
        // In production, use one of these approaches:
        // 1. PHP gnupg extension: https://www.php.net/manual/en/book.gnupg.php
        // 2. External command line gpg: exec('gpg --encrypt ...')
        // 3. JavaScript library on client side for encryption
        // 4. External service API

        // For development/demo purposes, we'll return a base64 encoded mock
        // The real implementation would use actual PGP encryption

        // Check if gnupg extension is available
        if (extension_loaded('gnupg')) {
            try {
                $gpg = new \gnupg();
                $gpg->seterrormode(\gnupg::ERROR_EXCEPTION);

                // Import the public key
                $info = $gpg->import($publicKey);

                if (!$info || !isset($info['fingerprint'])) {
                    throw new \Exception('Failed to import PGP public key');
                }

                // Add key for encryption
                $gpg->addencryptkey($info['fingerprint']);

                // Encrypt the message
                $encrypted = $gpg->encrypt($message);

                if (!$encrypted) {
                    throw new \Exception('Encryption failed');
                }

                return $encrypted;
            } catch (\Exception $e) {
                throw new \Exception('PGP encryption failed: ' . $e->getMessage());
            }
        }

        // Fallback: Mock encryption for development
        // WARNING: This is NOT secure and should NEVER be used in production
        $mock = "-----BEGIN PGP MESSAGE-----\n\n"
            . "*** DEVELOPMENT MODE - NO ACTUAL ENCRYPTION ***\n"
            . "In production, this would be properly encrypted with gnupg.\n\n"
            . "PLAINTEXT MESSAGE FOR TESTING:\n"
            . $message . "\n\n"
            . "*** Install php-gnupg extension for real encryption ***\n\n"
            . "-----END PGP MESSAGE-----";

        return $mock;
    }
}
