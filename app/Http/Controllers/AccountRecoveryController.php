<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AccountRecoveryController extends Controller
{
    /**
     * Show account recovery form (step 1: username + passphrases)
     */
    public function showRecoveryForm()
    {
        return view('auth.recovery.step1');
    }

    /**
     * Verify username and passphrases (step 1)
     */
    public function verifyPassphrases(Request $request)
    {
        // Multiple rate limiting layers for abuse prevention
        
        // 1. IP-based rate limiting (prevent brute force from single IP)
        $ipKey = 'recovery-ip:' . $request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 5)) {
            $seconds = RateLimiter::availableIn($ipKey);
            \Log::warning('Recovery rate limit exceeded', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return back()->withErrors([
                'error' => "Too many recovery attempts. Please try again in " . ceil($seconds / 60) . " minutes."
            ])->withInput();
        }

        $validated = $request->validate([
            'username_pri' => 'required|string|min:3|max:30',
            'passphrase_1' => 'required|string|min:5',
            'passphrase_2' => 'nullable|string|min:5',
        ]);

        // 2. Username-based rate limiting (prevent targeted attacks on specific accounts)
        $usernameKey = 'recovery-user:' . strtolower($validated['username_pri']);
        if (RateLimiter::tooManyAttempts($usernameKey, 3)) {
            $seconds = RateLimiter::availableIn($usernameKey);
            \Log::warning('Recovery attempt on locked account', [
                'username_pri' => $validated['username_pri'],
                'ip' => $request->ip(),
            ]);
            
            // OPSEC: Don't reveal if username exists - same error for all cases
            sleep(2); // Add delay to slow down attackers
            return back()->withErrors([
                'error' => "Too many attempts for this account. Please try again in " . ceil($seconds / 60) . " minutes."
            ])->withInput(['username_pri' => $validated['username_pri']]);
        }

        // Hit both rate limiters
        RateLimiter::hit($ipKey, 600); // 10 minutes
        RateLimiter::hit($usernameKey, 1800); // 30 minutes

        // SECURITY: Only find user by PRIVATE username (username_pri)
        // Public usernames are visible to everyone and should NOT be used for recovery
        $user = User::where('username_pri', $validated['username_pri'])->first();

        // OPSEC: Use constant-time comparison to prevent timing attacks
        // Always verify hashes even if user doesn't exist to prevent enumeration
        $dummyHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // Dummy bcrypt hash
        
        if (!$user) {
            // Perform dummy hash check to maintain constant time
            Hash::check($validated['passphrase_1'], $dummyHash);
            sleep(1); // Add delay
            
            // Log failed attempt
            \Log::warning('Recovery attempt on non-existent account', [
                'username_pri' => $validated['username_pri'],
                'ip' => $request->ip(),
            ]);
            
            // Generic error message (no username enumeration)
            return back()->withErrors([
                'error' => 'Invalid private username or passphrases. Please check your recovery information and try again.'
            ])->withInput(['username_pri' => $validated['username_pri']]);
        }

        // Verify primary passphrase
        $passphrase1Valid = Hash::check($validated['passphrase_1'], $user->passphrase_1);
        
        // Check secondary passphrase if it exists
        $passphrase2Valid = true; // Default to valid if not set
        if ($user->passphrase_2) {
            if (!empty($validated['passphrase_2'])) {
                $passphrase2Valid = Hash::check($validated['passphrase_2'], $user->passphrase_2);
            } else {
                $passphrase2Valid = false; // Secondary passphrase required but not provided
            }
        }

        // If either passphrase is invalid, return generic error
        if (!$passphrase1Valid || !$passphrase2Valid) {
            // Log failed verification
            \Log::warning('Recovery passphrase verification failed', [
                'user_id' => $user->id,
                'username_pub' => $user->username_pub,
                'ip' => $request->ip(),
                'passphrase_1_valid' => $passphrase1Valid,
                'passphrase_2_checked' => $user->passphrase_2 !== null,
            ]);
            
            sleep(1); // Add delay to slow down brute force
            
            // Generic error message (don't reveal which passphrase is wrong)
            return back()->withErrors([
                'error' => 'Invalid private username or passphrases. Please check your recovery information and try again.'
            ])->withInput(['username_pri' => $validated['username_pri']]);
        }

        // Success - clear rate limits for this IP and username
        RateLimiter::clear($ipKey);
        RateLimiter::clear($usernameKey);

        // Log successful verification (for security audit)
        \Log::info('Recovery passphrase verification successful', [
            'user_id' => $user->id,
            'username_pub' => $user->username_pub,
            'ip' => $request->ip(),
        ]);

        // Generate recovery token and store in session (OPSEC: short-lived, single-use)
        $recoveryToken = Str::random(64);
        session([
            'recovery_token' => $recoveryToken,
            'recovery_user_id' => $user->id,
            'recovery_expires_at' => now()->addMinutes(15), // 15 minute expiry
        ]);

        return redirect()->route('recovery.reset-password');
    }

    /**
     * Show password reset form (step 2)
     */
    public function showResetForm()
    {
        // Verify recovery session exists and not expired
        if (!session()->has('recovery_token') || 
            !session()->has('recovery_user_id') ||
            now()->greaterThan(session('recovery_expires_at'))) {
            
            session()->forget(['recovery_token', 'recovery_user_id', 'recovery_expires_at']);
            return redirect()->route('recovery.show')
                ->with('error', 'Recovery session expired. Please start again.');
        }

        return view('auth.recovery.step2');
    }

    /**
     * Reset password (step 2)
     */
    public function resetPassword(Request $request)
    {
        // Rate limit password reset attempts (even with valid session)
        $sessionKey = 'recovery-reset:' . session('recovery_token', 'unknown');
        if (RateLimiter::tooManyAttempts($sessionKey, 3)) {
            $seconds = RateLimiter::availableIn($sessionKey);
            \Log::warning('Password reset rate limit exceeded', [
                'ip' => $request->ip(),
                'user_id' => session('recovery_user_id'),
            ]);
            
            // Clear session on abuse
            session()->forget(['recovery_token', 'recovery_user_id', 'recovery_expires_at']);
            
            abort(429, 'Too many password reset attempts. Please start recovery process again.');
        }

        // Verify recovery session
        if (!session()->has('recovery_token') || 
            !session()->has('recovery_user_id') ||
            now()->greaterThan(session('recovery_expires_at'))) {
            
            session()->forget(['recovery_token', 'recovery_user_id', 'recovery_expires_at']);
            abort(403, 'Invalid or expired recovery session.');
        }

        RateLimiter::hit($sessionKey, 60); // 1 minute between attempts

        $validated = $request->validate([
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

        $user = User::findOrFail(session('recovery_user_id'));

        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Log password reset (security audit)
        \Log::info('Password reset via recovery system', [
            'user_id' => $user->id,
            'username_pub' => $user->username_pub,
            'ip' => $request->ip(),
        ]);

        // Clear recovery session and rate limits
        session()->forget(['recovery_token', 'recovery_user_id', 'recovery_expires_at']);
        RateLimiter::clear($sessionKey);

        // Log the user in
        Auth::login($user);

        // Force update last login timestamp
        $user->update(['last_login_at' => now()]);

        return redirect()->route('home')
            ->with('success', 'Password reset successfully! You are now logged in.');
    }
}
