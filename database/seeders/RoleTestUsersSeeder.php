<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleTestUsersSeeder extends Seeder
{
    /**
     * Cuentas de prueba fijas por rol.
     *
     * Las dejamos como updateOrCreate para poder re-seedear
     * sin duplicar usuarios y sin tener que limpiar la base.
     */
    public function run(): void
    {
        $users = [
            [
                'email' => 'admin.demo@almaconecta.com',
                'name' => 'Admin Demo',
                'password' => 'Admin1234!',
                'role' => 'admin',
                'account_status' => 'active',
            ],
            [
                'email' => 'provider.demo@almaconecta.com',
                'name' => 'Provider Demo',
                'password' => 'Provider1234!',
                'role' => 'provider',
                'account_status' => 'active',
            ],
            [
                'email' => 'client.demo@almaconecta.com',
                'name' => 'Client Demo',
                'password' => 'Client1234!',
                'role' => 'client',
                'account_status' => 'active',
                'document_type' => 'DNI',
                'document_number' => '00000000',
                'phone' => '+54 11 5555-0000',
            ],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'role' => $data['role'],
                    'account_status' => $data['account_status'],
                    'email_verified_at' => now(),
                    'approved_at' => now(),
                    'document_type' => $data['document_type'] ?? null,
                    'document_number' => $data['document_number'] ?? null,
                    'phone' => $data['phone'] ?? null,
                ]
            );

            // Limpieza explícita por si el usuario ya existía con datos parciales.
            $user->forceFill([
                'email_verified_at' => now(),
                'approved_at' => now(),
            ])->save();
        }
    }
}
