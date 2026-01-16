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
            auth()->login($user);

            // Role-based redirect with priority
            // Priority: admin > moderator > vendor > user
            if ($user->hasRole('admin')) {
                return redirect()->to(route('admin.dashboard'));
            } elseif ($user->hasRole('moderator')) {
                return redirect()->to(route('moderator.dashboard'));
            } elseif ($user->hasRole('vendor')) {
                return redirect()->to(route('vendor.dashboard'));
            } else {
                return redirect()->to(route('home'));
            }
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
