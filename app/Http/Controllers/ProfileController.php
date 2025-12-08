<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'public_username' => 'required|alpha_num|min:3|max:30|unique:users,username_pub,' . $user->id,
            'pgp_pub_key' => 'nullable|string|max:5000',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully!');
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
}
