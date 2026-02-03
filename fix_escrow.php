<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== FIXING ESCROW RECORD ===\n\n";

$escrow = \App\Models\EscrowWallet::find(2);
if (!$escrow) {
    echo "Escrow wallet not found\n";
    exit(1);
}

echo "Escrow Address: {$escrow->address}\n";

$xmrAddr = \App\Models\XmrAddress::where('address', $escrow->address)->first();
if (!$xmrAddr) {
    echo "XmrAddress not found for this escrow\n";
    exit(1);
}

echo "XmrAddress found:\n";
echo "  Account Index: {$xmrAddr->account_index}\n";
echo "  Address Index: {$xmrAddr->address_index}\n";

$escrow->account_index = $xmrAddr->account_index;
$escrow->address_index = $xmrAddr->address_index;
$escrow->wallet_name = 'teardrop_master';
$escrow->save();

echo "\n✅ Escrow updated successfully\n";
echo "  account_index: {$escrow->account_index}\n";
echo "  address_index: {$escrow->address_index}\n";
echo "  wallet_name: {$escrow->wallet_name}\n";
