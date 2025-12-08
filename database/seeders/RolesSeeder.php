<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['admin', 'user', 'moderator', 'vendor'];
        foreach ($roles as $role) {
            \App\Models\Role::updateOrCreate(['name' => $role]);
        }
    }
}
