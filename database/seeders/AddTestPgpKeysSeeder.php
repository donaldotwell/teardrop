<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AddTestPgpKeysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample PGP public key (for testing purposes)
        $samplePgpKey = <<<'PGP'
-----BEGIN PGP PUBLIC KEY BLOCK-----

mQINBGYqZh0BEADJvQKXPz8F3D6pN2wKjqL8vZxMN9yH2nQPLwRxZ5vFkNzV3M7Q
xYqL9pN8vK2fT4rX6pL3wZ9vQ2nY8pN7vK2fT4rX6pL3wZ9vQ2nY8pN7vK2fT4rX
6pL3wZ9vQ2nY8pN7vK2fT4rX6pL3wZ9vQ2nY8pN7vK2fT4rX6pL3wZ9vQ2nY8pN7
vK2fT4rX6pL3wZ9vQ2nY8pN7vK2fT4rX6pL3wZ9vQ2nY8pN7vK2fT4rX6pL3wZ9v
Q2nY8pN7vK2fT4rX6pL3wZ9vQ2nY8pN7vK2fT4rX6pL3wZ9vQ2nY8pN7vK2fT4rX
6pL3wZ9vQ2nY8pN7vK2fT4rX6pL3wZ9vQ2nY8pN7vK2fT4rX6pL3wZ9vQ2nY8pN7
vK2fT4rX6pL3wZ9vQ2nY8pN7vK2fT4rX6pL3wZ9vQ2nY8pN7vK2fT4rX6pL3wZ9v
Q2nY8pN7vK2fT4rX6pL3wZ9vQ2nY8pN7vK2fT4rX6pL3wZ9vQ2nY8pN7vK2fT4rX
6pL3wZ9vQ2nY8pN7vK2fT4rX6pL3wZ9vQ2nY8pN7vK2fT4rX6pL3wZ9vQ2nY8pN7
vK2fT4rX6pL3wZ9vQ2nY8pN7vK2fT4rX6pL3wZ9vQ2nY8pN7vK2fT4rX6pL3wZ9v
Q2nY8pN7vK2fT4rX6pL3wZ9vQ2nY8pN7vK2fT4rXABEBAAG0JUFkbWluaXN0cmF0
b3IgPGFkbWluQHRlYXJkcm9wLm1hcmtldD6JAk4EEwEKADgWIQR6mzA1B3C8D9E4
F6pN8vQ2nY8pN7vAUCZh0BEACQQD3wZ9vQ2nY8pN7vAhsDBgsJCAcKCwYVCAkKCw
IEFgIDAQIeBwIXgAAKCRD3wZ9vQ2nY8pN7vK2fT4rXABEBAAG5Ag0EZh0BEAEQAM
8N9yH2nQPLwRxZ5vFkNzV3M7QxYqL9pN8vK2fT4rX6pL3wZ9vQ2nY8pN7vK2fT4r
X6pL3wZ9vQ2nY8pN7vK2fT4rX6pL3wZ9vQ2nY8pN7vK2fT4rX6pL3wZ9vQ2nY8pN7
=ABCD
-----END PGP PUBLIC KEY BLOCK-----
PGP;

        // Get admin user
        $admin = User::where('username_pri', 'admin')->first();
        if ($admin) {
            $admin->update(['pgp_pub_key' => $samplePgpKey]);
            $this->command->info('Added PGP key to admin user');
        }

        // Get moderators and add keys
        $moderators = User::whereHas('roles', function ($query) {
            $query->where('name', 'moderator');
        })->get();

        foreach ($moderators as $index => $moderator) {
            // Generate slightly different keys for each moderator
            $modKey = str_replace('Administrator', 'Moderator ' . ($index + 1), $samplePgpKey);
            $modKey = str_replace('admin@teardrop.market', "moderator{$moderator->id}@teardrop.market", $modKey);
            
            $moderator->update(['pgp_pub_key' => $modKey]);
            $this->command->info("Added PGP key to moderator: {$moderator->username_pub}");
        }

        $this->command->info('PGP keys added successfully to all staff members.');
    }
}

