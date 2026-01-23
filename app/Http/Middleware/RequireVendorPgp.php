<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireVendorPgp
{
    /**
     * Handle an incoming request.
     *
     * Ensures vendors have a PGP public key configured before accessing vendor features.
     * PGP key is required for encrypting customer delivery addresses.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Only check if user has vendor role
            if ($user->hasRole('vendor') && empty($user->pgp_pub_key)) {
                return redirect()->route('profile.pgp')
                    ->with('error', 'You must configure a PGP public key before accessing vendor features. This is required for encrypting customer delivery addresses.');
            }
        }

        return $next($request);
    }
}
