<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Display the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm() : \Illuminate\View\View
    {
        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request) : \Illuminate\Http\RedirectResponse
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username_pri', $credentials['username'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {

            // If user is not active, deny login
            if ($user->status !== 'active') {
                return back()->withErrors([
                    'username' => 'Your account is ' . $user->status . '. Please contact support.',
                ]);
            }

            // If user has a PGP key, require MFA before completing login
            if (!empty($user->pgp_pub_key)) {
                return $this->initiatePgpLoginChallenge($user, $request);
            }

            // No PGP key — standard login
            auth()->login($user);

            // Update last login timestamp
            $user->update(['last_login_at' => now()]);

            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'username' => 'The provided credentials are incorrect.',
        ]);
    }

    /**
     * Display the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegisterForm() : \Illuminate\View\View
    {
        return view('auth.register');
    }

    /**
     * Handle a registration attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request) : \Illuminate\Http\RedirectResponse
    {
        $credentials = $request->validate([
            'private_username' => 'required|alpha_num|min:3|max:30|unique:users,username_pri',
            'public_username' => 'required|alpha_num|min:3|max:30|unique:users,username_pub|different:private_username',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
//                    ->uncompromised(),
            ],
        ]);

        $user = User::create([
            'username_pri' => $credentials['private_username'],
            'username_pub' => $credentials['public_username'],
            'password' => Hash::make($credentials['password']),
            // Set default/placeholder values for required fields
            'pin' => Hash::make('000000'), // Temporary PIN
            'passphrase_1' => Hash::make('temporary_passphrase_' . Str::random(8)),
        ]);

        // Assign the 'user' role to the user
        $user->assignRoleByName('user');

        auth()->login($user);

        // Redirect to profile completion page
        return redirect()->route('profile.complete')
            ->with('success', 'Account created! Please complete your security settings.');
    }

    /**
     * Initiate PGP login challenge for MFA.
     * Called when a user with a PGP key passes username/password.
     */
    private function initiatePgpLoginChallenge(User $user, Request $request): \Illuminate\Http\RedirectResponse
    {
        // Generate a random verification code
        $verificationCode = Str::upper(Str::random(12));

        // Create the challenge message
        $challengeMessage = strtoupper(config('app.name')) . " LOGIN VERIFICATION\n\n"
            . "Username: {$user->username_pub}\n"
            . "Verification Code: {$verificationCode}\n"
            . "Timestamp: " . now()->toDateTimeString() . "\n\n"
            . "Decrypt this message with your private key and submit the verification code to complete login.\n"
            . "This code expires in 10 minutes.";

        // Encrypt the message with the user's stored PGP public key
        try {
            $encryptedMessage = $this->encryptWithPgp($challengeMessage, trim($user->pgp_pub_key));
        } catch (\Exception $e) {
            \Log::error('PGP login challenge encryption failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            // If PGP encryption fails, fall back to standard login
            // (don't lock user out because of server-side PGP issues)
            auth()->login($user);
            $user->update(['last_login_at' => now()]);

            return $this->redirectBasedOnRole($user);
        }

        // Store the challenge in session (do NOT log in the user yet)
        $request->session()->put('pgp_login_challenge', [
            'user_id' => $user->id,
            'code' => $verificationCode,
            'encrypted_message' => $encryptedMessage,
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
        ]);

        return redirect()->route('login.pgp-challenge');
    }

    /**
     * Show the PGP login challenge page.
     */
    public function showPgpLoginChallenge(Request $request)
    {
        $challenge = $request->session()->get('pgp_login_challenge');

        if (!$challenge) {
            return redirect()->route('login')
                ->withErrors(['username' => 'No active PGP challenge. Please log in again.']);
        }

        // Check if expired
        if ($challenge['expires_at'] < now()->timestamp) {
            $request->session()->forget('pgp_login_challenge');
            return redirect()->route('login')
                ->withErrors(['username' => 'PGP challenge expired. Please log in again.']);
        }

        // Check max attempts
        if ($challenge['attempts'] >= 5) {
            $request->session()->forget('pgp_login_challenge');
            return redirect()->route('login')
                ->withErrors(['username' => 'Maximum PGP verification attempts exceeded. Please log in again.']);
        }

        return view('auth.pgp-challenge', [
            'encryptedMessage' => $challenge['encrypted_message'],
            'attemptsRemaining' => 5 - $challenge['attempts'],
            'expiresAt' => $challenge['expires_at'],
        ]);
    }

    /**
     * Verify the PGP login challenge code and complete login.
     */
    public function verifyPgpLoginChallenge(Request $request): \Illuminate\Http\RedirectResponse
    {
        $challenge = $request->session()->get('pgp_login_challenge');

        if (!$challenge) {
            return redirect()->route('login')
                ->withErrors(['username' => 'No active PGP challenge. Please log in again.']);
        }

        // Check if expired
        if ($challenge['expires_at'] < now()->timestamp) {
            $request->session()->forget('pgp_login_challenge');
            return redirect()->route('login')
                ->withErrors(['username' => 'PGP challenge expired. Please log in again.']);
        }

        // Check max attempts
        if ($challenge['attempts'] >= 5) {
            $request->session()->forget('pgp_login_challenge');
            return redirect()->route('login')
                ->withErrors(['username' => 'Maximum PGP verification attempts exceeded. Please log in again.']);
        }

        $validated = $request->validate([
            'verification_code' => 'required|string',
        ]);

        $submittedCode = strtoupper(trim($validated['verification_code']));

        if ($submittedCode !== $challenge['code']) {
            // Failed attempt — increment counter
            $challenge['attempts']++;
            $request->session()->put('pgp_login_challenge', $challenge);

            $attemptsLeft = 5 - $challenge['attempts'];

            if ($attemptsLeft <= 0) {
                $request->session()->forget('pgp_login_challenge');
                return redirect()->route('login')
                    ->withErrors(['username' => 'Maximum PGP verification attempts exceeded. Please log in again.']);
            }

            return back()->withErrors([
                'verification_code' => "Incorrect verification code. You have {$attemptsLeft} attempt(s) remaining.",
            ]);
        }

        // Code verified — complete login
        $user = User::find($challenge['user_id']);

        if (!$user || $user->status !== 'active') {
            $request->session()->forget('pgp_login_challenge');
            return redirect()->route('login')
                ->withErrors(['username' => 'Account is no longer available.']);
        }

        // Clear the challenge
        $request->session()->forget('pgp_login_challenge');

        // Log in the user
        auth()->login($user);
        $user->update(['last_login_at' => now()]);

        return $this->redirectBasedOnRole($user);
    }

    /**
     * Role-based redirect after successful login.
     */
    private function redirectBasedOnRole(User $user): \Illuminate\Http\RedirectResponse
    {
        if ($user->hasRole('admin')) {
            return redirect()->to(route('admin.dashboard'));
        } elseif ($user->hasRole('moderator')) {
            return redirect()->to(route('moderator.dashboard'));
        } elseif ($user->hasRole('vendor')) {
            return redirect()->to(route('vendor.dashboard'));
        }

        return redirect()->to(route('home'));
    }

    /**
     * Encrypt a message using PGP public key with gnupg extension.
     * Reusable for login MFA challenges.
     */
    private function encryptWithPgp(string $message, string $publicKey): string
    {
        if (!extension_loaded('gnupg')) {
            throw new \Exception('PGP encryption requires the php-gnupg extension.');
        }

        $tempGpgHome = '/tmp/teardrop_gpg_' . uniqid('', true);

        try {
            if (!mkdir($tempGpgHome, 0700, true)) {
                throw new \Exception('Failed to create temporary GPG directory');
            }

            putenv("GNUPGHOME={$tempGpgHome}");

            $publicKey = trim(str_replace(["\r\n", "\r"], "\n", $publicKey));

            if (!preg_match('/-----BEGIN PGP PUBLIC KEY BLOCK-----.*-----END PGP PUBLIC KEY BLOCK-----/s', $publicKey)) {
                throw new \Exception('Invalid PGP key format.');
            }

            $gpg = new \gnupg();
            $gpg->seterrormode(\gnupg::ERROR_EXCEPTION);
            $gpg->setarmor(1);

            $importResult = $gpg->import($publicKey);

            if (!$importResult) {
                throw new \Exception('Key import failed.');
            }

            $fingerprint = null;
            if (is_array($importResult)) {
                $fingerprint = $importResult['fingerprint'] ?? null;
            } elseif (is_object($importResult)) {
                $fingerprint = $importResult->fingerprint ?? null;
            }

            if (empty($fingerprint)) {
                $allKeys = $gpg->keyinfo("");
                if (!empty($allKeys)) {
                    $lastKey = end($allKeys);
                    $fingerprint = $lastKey['subkeys'][0]['fingerprint'] ?? null;
                }
            }

            if (empty($fingerprint)) {
                throw new \Exception('Failed to extract fingerprint from key.');
            }

            if (!$gpg->addencryptkey($fingerprint)) {
                throw new \Exception('Failed to add encryption key.');
            }

            $encrypted = $gpg->encrypt($message);

            if (!$encrypted || empty($encrypted)) {
                throw new \Exception('Encryption produced empty result.');
            }

            return $encrypted;

        } finally {
            if (isset($tempGpgHome) && is_dir($tempGpgHome)) {
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
            putenv("GNUPGHOME");
        }
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request) : \Illuminate\Http\RedirectResponse
    {
        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
