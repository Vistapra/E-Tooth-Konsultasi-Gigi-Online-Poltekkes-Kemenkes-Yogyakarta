<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat peran 'owner' jika belum ada
        $ownerRole = Role::where('name', 'owner')->first();
        if (!$ownerRole) {
            $ownerRole = Role::create(['name' => 'owner']);
        }

        // Membuat peran 'buyer' jika belum ada
        $buyerRole = Role::where('name', 'buyer')->first();
        if (!$buyerRole) {
            $buyerRole = Role::create(['name' => 'buyer']);
        }

        // Membuat peran 'doctor' jika belum ada
        $doctorRole = Role::where('name', 'doctor')->first();
        if (!$doctorRole) {
            $doctorRole = Role::create(['name' => 'doctor']);
        }
    }
}
