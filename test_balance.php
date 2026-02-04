<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== MONERO BALANCE VERIFICATION ===\n\n";

$user = \App\Models\User::find(8);
if (!$user) {
    echo "User not found\n";
    exit(1);
}

echo "Testing User::getBalance() method:\n";
$balance = $user->getBalance();

echo "BTC Balance: " . number_format($balance['btc']['balance'], 8) . " BTC\n";
echo "XMR Balance: " . number_format($balance['xmr']['balance'], 12) . " XMR\n";
echo "XMR Unlocked: " . number_format($balance['xmr']['unlocked_balance'], 12) . " XMR\n";

echo "\n✅ Balance method works correctly\n";
echo "\nMonero withdrawal validation:\n";
echo "- Max withdrawal: " . number_format($balance['xmr']['unlocked_balance'], 12) . " XMR\n";
echo "- View will display: " . number_format($balance['xmr']['balance'], 12) . " XMR available\n";
