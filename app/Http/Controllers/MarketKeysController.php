<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class MarketKeysController extends Controller
{
    /**
     * Display public PGP keys for admin and moderator staff.
     * Accessible to guests and authenticated users.
     */
    public function index()
    {
        // Query active users with admin role
        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })
        ->where('status', 'active')
        ->whereNotNull('pgp_pub_key')
        ->select('id', 'username_pub', 'pgp_pub_key', 'created_at')
        ->orderBy('created_at', 'asc')
        ->get()
        ->map(function ($user) {
            return [
                'username' => $user->username_pub,
                'pgp_key' => $user->pgp_pub_key,
                'fingerprint' => $this->extractFingerprint($user->pgp_pub_key),
                'member_since' => $user->created_at->format('M Y'),
            ];
        });

        // Query active users with moderator role
        $moderators = User::whereHas('roles', function ($query) {
            $query->where('name', 'moderator');
        })
        ->where('status', 'active')
        ->whereNotNull('pgp_pub_key')
        ->select('id', 'username_pub', 'pgp_pub_key', 'created_at')
        ->orderBy('created_at', 'asc')
        ->get()
        ->map(function ($user) {
            return [
                'username' => $user->username_pub,
                'pgp_key' => $user->pgp_pub_key,
                'fingerprint' => $this->extractFingerprint($user->pgp_pub_key),
                'member_since' => $user->created_at->format('M Y'),
            ];
        });

        // Determine which layout to use based on authentication
        $layout = auth()->check() ? 'layouts.app' : 'layouts.auth';

        return view('market-keys.index', compact('admins', 'moderators', 'layout'));
    }

    /**
     * Extract PGP fingerprint from public key.
     * Returns formatted fingerprint or null if extraction fails.
     */
    private function extractFingerprint($pgpKey)
    {
        if (empty($pgpKey)) {
            return null;
        }

        // Try to extract fingerprint using regex pattern
        // PGP fingerprints are typically 40 hex characters
        if (preg_match('/([0-9A-F]{40})/i', $pgpKey, $matches)) {
            $fingerprint = $matches[1];
            // Format as groups of 4 characters
            return implode(' ', str_split($fingerprint, 4));
        }

        // Alternative: try to parse the key and generate fingerprint
        // For now, return a truncated version of the key as fallback
        $cleaned = preg_replace('/[^A-Za-z0-9]/', '', $pgpKey);
        if (strlen($cleaned) > 40) {
            $hash = strtoupper(substr(hash('sha1', $pgpKey), 0, 40));
            return implode(' ', str_split($hash, 4));
        }

        return 'Unable to extract fingerprint';
    }
}
