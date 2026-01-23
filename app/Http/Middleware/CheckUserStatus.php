<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Block banned users immediately
            if ($user->status === 'banned') {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->with('error', 'Your account has been banned. Contact support for more information.');
            }
            
            // Block inactive users from performing actions (except viewing profile/logout)
            if ($user->status === 'inactive') {
                // Allow access to profile and logout routes only
                $allowedRoutes = ['profile.show', 'profile.complete', 'logout'];
                if (!in_array($request->route()->getName(), $allowedRoutes)) {
                    return redirect()->route('profile.show')->with('error', 'Your account is inactive. Please contact support to reactivate.');
                }
            }
        }

        return $next($request);
    }
}
