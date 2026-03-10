<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => 'admin@caps-panel.com',
        ], [
            'name' => 'Admin CAPS Panel',
            'password' => Hash::make('caps$2026@panel'),
            'email_verified_at' => now(),
            'role' => 'admin',
            'tenant_id' => Tenant::query()->first()->id,
        ]);
    }
}
