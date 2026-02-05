<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BotProtectionMiddleware
{
    /**
     * Routes that should be excluded from bot protection
     *
     * @var array
     */
    protected $except = [
        'bot-challenge',
        'bot-challenge/verify',
        'bot-challenge/image',
        'market-keys',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip bot protection for excluded routes
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        // Check if user is locked out
        if ($this->isLockedOut($request)) {
            return redirect()->route('bot-challenge.locked');
        }

        // Check if user has passed the challenge
        if (!$this->hasPassedChallenge($request)) {
            // Store the intended URL
            session(['bot_challenge_intended' => $request->fullUrl()]);
            return redirect()->route('bot-challenge');
        }

        return $next($request);
    }

    /**
     * Determine if the request should pass through without challenge
     */
    protected function shouldPassThrough(Request $request): bool
    {
        foreach ($this->except as $except) {
            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has passed the bot challenge
     */
    protected function hasPassedChallenge(Request $request): bool
    {
        // Session-based only, no expiry
        return session('bot_challenge_passed', false);
    }

    /**
     * Check if user is currently locked out
     */
    protected function isLockedOut(Request $request): bool
    {
        $lockedUntil = session('bot_challenge_locked_until');

        if (!$lockedUntil) {
            return false;
        }

        if (time() < $lockedUntil) {
            return true;
        }

        // Lockout expired - clear it
        session()->forget(['bot_challenge_locked_until', 'bot_challenge_failed_attempts']);

        return false;
    }
}
