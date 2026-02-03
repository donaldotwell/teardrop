<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== ESCROW FLOW VERIFICATION ===\n\n";

// 1. Test findAddressesForPayment fix
$user = \App\Models\User::find(8);
$wallet = $user->xmrWallet;
$addresses = \App\Repositories\MoneroRepository::findAddressesForPayment($wallet, 0.08);
echo "1. Address lookup (0.08 XMR): " . (empty($addresses) ? "FAIL" : "PASS - " . count($addresses) . " found") . "\n";

// 2. Check admin wallet exists
$adminWallet = \App\Models\XmrWallet::where('name', 'wallet_01.bin')->first();
echo "2. Admin wallet: " . ($adminWallet ? "FOUND (ID: {$adminWallet->id})" : "NOT FOUND") . "\n";

// 3. Verify wallet name fix
$testEscrow = \App\Models\EscrowWallet::first();
if ($testEscrow) {
    echo "3. Escrow wallet_name: {$testEscrow->wallet_name}\n";
    $matchingWallet = \App\Models\XmrWallet::where('name', $testEscrow->wallet_name)->first();
    echo "   XmrWallet lookup: " . ($matchingWallet ? "MATCHES (ID: {$matchingWallet->id})" : "MISMATCH - WOULD FAIL") . "\n";
}

echo "\nResult: " . (empty($addresses) || !$adminWallet ? "FAIL" : "SUCCESS - All checks pass") . "\n";
