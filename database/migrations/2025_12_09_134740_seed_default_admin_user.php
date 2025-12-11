<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // Por las dudas, sólo si existe la tabla users
        if (! Schema::hasTable('users')) {
            return;
        }

        $email = 'admin@gmail.com';

        // Si ya existe un usuario con ese mail, no hacemos nada
        $exists = DB::table('users')->where('email', $email)->exists();

        if (! $exists) {
            DB::table('users')->insert([
                'name'              => 'Admin',
                'email'             => $email,
                'password'          => Hash::make('$Admin1234'),
                'role'              => 'admin',          // según tu schema
                'account_status'    => 'active',         // según tu schema
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        DB::table('users')
            ->where('email', 'admin@gmail.com')
            ->delete();
    }
};
