<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUsersController extends Controller
{
    /**
     * Display users management page
     */
    public function index(Request $request)
    {
        $query = User::query()->withCount('orders');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('username_pub', 'like', "%{$search}%")
                    ->orWhere('username_pri', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('trust_level')) {
            $query->where('trust_level', $request->get('trust_level'));
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->get('role'));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        // Calculate stats
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'vendors' => User::where('vendor_level', '>', 0)->count(),
            'banned_users' => User::where('status', 'banned')->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Show user details
     */
    public function show(User $user)
    {
        $user->load(['orders.listing', 'wallets', 'roles']);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show user edit form
     */
    public function edit(User $user)
    {
        $user->load(['orders', 'wallets']);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username_pub' => 'required|string|max:30|unique:users,username_pub,' . $user->id,
            'trust_level' => 'required|integer|min:1|max:10',
            'vendor_level' => 'required|integer|min:0|max:10',
            'status' => 'required|in:active,inactive,banned',
        ]);

        // If user is being made a vendor and wasn't before
        if ($validated['vendor_level'] > 0 && $user->vendor_level == 0) {
            $validated['vendor_since'] = now();
        }

        // If user is being removed as vendor
        if ($validated['vendor_level'] == 0 && $user->vendor_level > 0) {
            $validated['vendor_since'] = null;
        }

        $user->update($validated);

        return redirect()->route('admin.users.show', $user)
            ->with('success', "User {$user->username_pub} updated successfully.");
    }

    /**
     * Ban user
     */
    public function ban(User $user)
    {
        if ($user->hasRole('admin')) {
            return redirect()->back()
                ->with('error', 'Cannot ban admin users.');
        }

        $user->update(['status' => 'banned']);

        return redirect()->back()
            ->with('success', "User {$user->username_pub} has been banned.");
    }

    /**
     * Unban user
     */
    public function unban(User $user)
    {
        $user->update(['status' => 'active']);

        return redirect()->back()
            ->with('success', "User {$user->username_pub} has been unbanned.");
    }

    /**
     * Promote user to vendor (admin bypass of conversion fee)
     */
    public function promoteToVendor(User $user)
    {
        // Check if user is already a vendor
        if ($user->hasRole('vendor')) {
            return redirect()->back()
                ->with('error', 'User is already a vendor.');
        }

        // Update vendor level and set vendor_since timestamp
        $user->update([
            'vendor_level' => 1,
        ]);

        // Assign vendor role
        $user->assignRoleByName('vendor');

        return redirect()->back()
            ->with('success', "User {$user->username_pub} has been promoted to vendor.");
    }

    /**
     * Export users to CSV
     */
    public function export(Request $request)
    {
        $query = User::query()->withCount('orders');

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('username_pub', 'like', "%{$search}%")
                    ->orWhere('username_pri', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('trust_level')) {
            $query->where('trust_level', $request->get('trust_level'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'User ID',
                'Public Username',
                'Private Username',
                'Trust Level',
                'Vendor Level',
                'Status',
                'Total Orders',
                'Total Spent',
                'Vendor Since',
                'Created At',
                'Last Login',
                'Last Seen',
            ]);

            foreach ($users as $user) {
                $totalSpent = $user->orders()
                    ->where('status', 'completed')
                    ->sum('usd_price');

                fputcsv($file, [
                    $user->id,
                    $user->username_pub,
                    $user->username_pri,
                    $user->trust_level,
                    $user->vendor_level,
                    $user->status,
                    $user->orders_count ?? 0,
                    number_format($totalSpent, 2),
                    $user->vendor_since ? $user->vendor_since->format('Y-m-d') : '',
                    $user->created_at->format('Y-m-d H:i:s'),
                    $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : '',
                    $user->last_seen ? $user->last_seen->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Reset user password (admin function)
     */
    public function resetPassword(User $user)
    {
        // Generate temporary password
        $tempPassword = Str::random(12);

        $user->update([
            'password' => Hash::make($tempPassword)
        ]);

        // In a real application, you'd email this to the user
        // For now, we'll just show it in a flash message
        return redirect()->back()
            ->with('success', "Password reset for {$user->username_pub}. Temporary password: {$tempPassword}")
            ->with('warning', 'Make sure to securely communicate this password to the user.');
    }

    /**
     * View user's wallet transactions
     */
    public function walletTransactions(User $user)
    {
        $user->load(['wallets.transactions' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        return view('admin.users.wallet-transactions', compact('user'));
    }

    /**
     * Adjust user wallet balance (admin function)
     */
    public function adjustBalance(Request $request, User $user)
    {
        $validated = $request->validate([
            'currency' => 'required|in:btc,xmr',
            'amount' => 'required|numeric',
            'type' => 'required|in:credit,debit',
            'reason' => 'required|string|max:255',
        ]);

        $wallet = $user->wallets()->where('currency', $validated['currency'])->first();

        if (!$wallet) {
            return redirect()->back()
                ->with('error', 'User does not have a wallet for this currency.');
        }

        $amount = $validated['type'] === 'credit' ?
            abs($validated['amount']) :
            -abs($validated['amount']);

        // Check if debit would result in negative balance
        if ($amount < 0 && ($wallet->balance + $amount) < 0) {
            return redirect()->back()
                ->with('error', 'Insufficient balance for this debit.');
        }

        // Create transaction record
        $wallet->transactions()->create([
            'amount' => $amount,
            'type' => $validated['type'] === 'credit' ? 'deposit' : 'withdrawal',
            'comment' => "Admin adjustment: {$validated['reason']}",
            'confirmed_at' => now(),
            'completed_at' => now(),
        ]);

        // Update wallet balance
        $wallet->increment('balance', $amount);

        $action = $validated['type'] === 'credit' ? 'credited' : 'debited';

        return redirect()->back()
            ->with('success', "Successfully {$action} {$validated['amount']} {$validated['currency']} to {$user->username_pub}'s wallet.");
    }

    /**
     * Toggle early finalization access for vendor
     */
    public function toggleEarlyFinalizationAccess(User $user)
    {
        if ($user->vendor_level < 1) {
            return redirect()->back()->withErrors([
                'error' => 'User must be a vendor to have early finalization access.'
            ]);
        }

        $newStatus = !$user->early_finalization_enabled;
        $user->update(['early_finalization_enabled' => $newStatus]);

        // Create audit log
        \App\Models\AuditLog::log('early_finalization_toggled', $user->id, [
            'enabled' => $newStatus,
            'toggled_by' => auth()->id(),
        ]);

        $status = $newStatus ? 'enabled' : 'disabled';

        return redirect()->back()
            ->with('success', "Early finalization access {$status} for {$user->username_pub}.");
    }
}
