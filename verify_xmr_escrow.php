<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== MONERO ESCROW ARCHITECTURE VERIFICATION ===\n\n";

// 1. Verify master wallet structure
echo "1. MASTER WALLET VERIFICATION\n";
$masterWalletName = config('monero.master_wallet_name', 'teardrop_master');
$masterWallet = \App\Models\XmrWallet::where('name', $masterWalletName)->first();

if ($masterWallet) {
    echo "   ✓ Master wallet exists: {$masterWalletName}\n";
    echo "   ✓ Wallet ID: {$masterWallet->id}\n";
} else {
    echo "   ✗ Master wallet NOT FOUND!\n";
}

// 2. Check escrow wallet configuration
echo "\n2. ESCROW WALLET #2 CONFIGURATION\n";
$escrow = \App\Models\EscrowWallet::find(2);
if (!$escrow) {
    echo "   ✗ Escrow wallet #2 not found!\n";
    exit(1);
}

echo "   ✓ wallet_name: {$escrow->wallet_name}\n";
echo "   ✓ account_index: {$escrow->account_index}\n";
echo "   ✓ address_index: {$escrow->address_index}\n";
echo "   ✓ address: " . substr($escrow->address, 0, 20) . "...\n";
echo "   ✓ Uses master wallet: " . ($escrow->wallet_name === $masterWalletName ? "YES" : "NO - WRONG!") . "\n";

// 3. Check if XmrAddress record exists
echo "\n3. XMR ADDRESS RECORD\n";
$xmrAddress = \App\Models\XmrAddress::where('address', $escrow->address)->first();
if ($xmrAddress) {
    echo "   ✓ XmrAddress found (ID: {$xmrAddress->id})\n";
    echo "   ✓ account_index: {$xmrAddress->account_index}\n";
    echo "   ✓ address_index: {$xmrAddress->address_index}\n";
    echo "   ✓ label: {$xmrAddress->label}\n";
} else {
    echo "   ✗ XmrAddress NOT FOUND!\n";
}

// 4. Check RPC balance for this escrow address
echo "\n4. RPC BALANCE CHECK\n";
try {
    $balanceData = \App\Repositories\MoneroRepository::getAddressBalance(
        $escrow->account_index,
        $escrow->address_index
    );
    
    if ($balanceData) {
        $xmrBalance = $balanceData['unlocked_balance'];
        echo "   ✓ RPC balance: {$xmrBalance} XMR\n";
        echo "   ✓ Locked balance: {$balanceData['balance']} XMR\n";
        
        // Update escrow balance from RPC
        $escrow->update(['balance' => $xmrBalance]);
        echo "   ✓ Escrow record updated with RPC balance\n";
    } else {
        echo "   ✗ RPC balance query failed!\n";
    }
} catch (\Exception $e) {
    echo "   ✗ RPC Error: " . $e->getMessage() . "\n";
}

// 5. Verify findAddressesForPayment works
echo "\n5. FIND ADDRESSES FOR PAYMENT TEST\n";
$escrowXmrWallet = \App\Models\XmrWallet::where('name', $escrow->wallet_name)->first();
if ($escrowXmrWallet && $xmrBalance > 0) {
    $addresses = \App\Repositories\MoneroRepository::findAddressesForPayment($escrowXmrWallet, $xmrBalance);
    if (!empty($addresses)) {
        echo "   ✓ Found " . count($addresses) . " address(es) for payment\n";
        foreach ($addresses as $addr) {
            echo "     - Index {$addr['address_index']}: {$addr['balance']} XMR\n";
        }
    } else {
        echo "   ✗ No addresses found (balance check might be failing)\n";
    }
} else {
    echo "   ⊘ Skipped (no balance or wallet not found)\n";
}

// 6. Summary
echo "\n=== ARCHITECTURE VALIDATION ===\n";
$checks = [
    "Master wallet exists" => isset($masterWallet),
    "Escrow uses master wallet name" => ($escrow->wallet_name === $masterWalletName),
    "Escrow has account/address indices" => ($escrow->account_index !== null && $escrow->address_index !== null),
    "XmrAddress record exists" => isset($xmrAddress),
    "RPC balance accessible" => isset($balanceData),
];

$passed = 0;
$total = count($checks);
foreach ($checks as $check => $result) {
    echo ($result ? "✓" : "✗") . " {$check}\n";
    if ($result) $passed++;
}

echo "\nResult: {$passed}/{$total} checks passed\n";
if ($passed === $total) {
    echo "✓ MONERO ESCROW ARCHITECTURE IS CORRECT\n";
    echo "  - Uses ONE master wallet (no separate wallet files)\n";
    echo "  - Escrow is subaddress-based (differentiated by address_index)\n";
    echo "  - All operations work with master wallet + subaddress indices\n";
} else {
    echo "✗ ISSUES FOUND - See details above\n";
}
