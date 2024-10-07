<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $roles = ['owner', 'doctor', 'buyer'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
        $ownerRole = Role::where('name', 'owner')->first();

        $userOwner = User::firstOrCreate(
            ['email' => 'e-tooth@owner.com'],
            [
                'name' => 'E-Tooth',
                'password' => Hash::make('e-tooth'),
                'role' => 'owner'
            ]
        );


        $userOwner->assignRole($ownerRole);

        User::all()->each(function ($user) {
            if (!$user->hasAnyRole()) {
                $user->assignRole($user->role);
            }
        });
    }
}