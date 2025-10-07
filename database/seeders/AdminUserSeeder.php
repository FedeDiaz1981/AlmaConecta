<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
{
    \App\Models\User::updateOrCreate(
        ['email' => 'admin@almaconecta.com'],
        [
            'name' => 'Admin',
            'password' => \Illuminate\Support\Facades\Hash::make('TuPassFuerte123!'),
            'role' => 'admin',
            'account_status' => 'active',
            'email_verified_at' => now(),
        ]
    );
}
}
