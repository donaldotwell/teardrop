<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== MODERATOR DISPUTE RELATIONSHIP TEST ===\n\n";

// Find a moderator user
$moderator = \App\Models\User::whereHas('roles', function($q) {
    $q->where('name', 'moderator');
})->first();

if (!$moderator) {
    echo "No moderator found in database\n";
    exit(1);
}

echo "Moderator: {$moderator->username_pub} (ID: {$moderator->id})\n\n";

// Test the new relationship
echo "Testing User->moderatorDisputes() relationship:\n";
$disputes = $moderator->moderatorDisputes;
echo "  Found {$disputes->count()} disputes assigned to this moderator\n";

// Test open disputes
$openDisputes = $moderator->moderatorDisputes()->open()->get();
echo "  Open disputes: {$openDisputes->count()}\n";

// Test Dispute->assignedModerator relationship (inverse)
$dispute = \App\Models\Dispute::whereNotNull('assigned_moderator_id')->first();
if ($dispute) {
    echo "\nTesting Dispute->assignedModerator() relationship:\n";
    echo "  Dispute UUID: {$dispute->uuid}\n";
    $assignedMod = $dispute->assignedModerator;
    if ($assignedMod) {
        echo "  Assigned Moderator: {$assignedMod->username_pub} (ID: {$assignedMod->id})\n";
    } else {
        echo "  No moderator loaded (relationship issue)\n";
    }
}

echo "\n✅ Relationship tests complete\n";
