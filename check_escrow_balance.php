<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== ESCROW BALANCE CHECK ===\n\n";

$escrow = \App\Models\EscrowWallet::find(2);
echo "Escrow ID: {$escrow->id}\n";
echo "Address: {$escrow->address}\n";
echo "DB Balance: {$escrow->balance}\n";
echo "Account Index: {$escrow->account_index}\n";
echo "Address Index: {$escrow->address_index}\n\n";

// Check RPC balance
try {
    $balanceData = \App\Repositories\MoneroRepository::getAddressBalance(
        $escrow->account_index, 
        $escrow->address_index
    );
    
    if ($balanceData) {
        echo "RPC Balance: " . ($balanceData['balance'] / 1e12) . " XMR\n";
        echo "RPC Unlocked: " . ($balanceData['unlocked_balance'] / 1e12) . " XMR\n";
    } else {
        echo "RPC: No balance data\n";
    }
} catch (\Exception $e) {
    echo "RPC Error: " . $e->getMessage() . "\n";
}

// Check transactions to this address
echo "\nTransactions:\n";
$txs = \App\Models\XmrTransaction::where('address', $escrow->address)->get();
foreach ($txs as $tx) {
    echo "- {$tx->type}: {$tx->amount} XMR (status: {$tx->status})\n";
}

if ($txs->isEmpty()) {
    echo "No transactions found\n";
}
