<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('profile_service')) {
            // 1) Si NO existe la columna id, la agregamos (y ajustamos PK si hace falta)
            if (! Schema::hasColumn('profile_service', 'id')) {
                $driver = Schema::getConnection()->getDriverName();

                if ($driver === 'pgsql') {
                    // Si la PK actual es compuesta, la eliminamos
                    DB::statement('ALTER TABLE profile_service DROP CONSTRAINT IF EXISTS profile_service_pkey');
                    // Agregamos columna id autoincremental y la declaramos PK
                    DB::statement('ALTER TABLE profile_service ADD COLUMN id bigserial');
                    DB::statement('ALTER TABLE profile_service ADD PRIMARY KEY (id)');

                    // Aseguramos unicidad del par (profile_id, service_id) e índices
                    DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS profile_service_profile_id_service_id_unique ON profile_service (profile_id, service_id)');
                    DB::statement('CREATE INDEX IF NOT EXISTS profile_service_profile_id_index ON profile_service (profile_id)');
                    DB::statement('CREATE INDEX IF NOT EXISTS profile_service_service_id_index ON profile_service (service_id)');
                } else {
                    // MySQL / otros
                    Schema::table('profile_service', function (Blueprint $t) {
                        $t->bigIncrements('id')->first();
                        $t->unique(['profile_id', 'service_id'], 'profile_service_profile_id_service_id_unique');
                        $t->index('profile_id');
                        $t->index('service_id');
                    });
                }
            } else {
                // 2) Ya existe id: garantizamos índices/únicos
                if (Schema::getConnection()->getDriverName() === 'pgsql') {
                    DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS profile_service_profile_id_service_id_unique ON profile_service (profile_id, service_id)');
                    DB::statement('CREATE INDEX IF NOT EXISTS profile_service_profile_id_index ON profile_service (profile_id)');
                    DB::statement('CREATE INDEX IF NOT EXISTS profile_service_service_id_index ON profile_service (service_id)');
                } else {
                    Schema::table('profile_service', function (Blueprint $t) {
                        $t->unique(['profile_id', 'service_id'], 'profile_service_profile_id_service_id_unique');
                        $t->index('profile_id');
                        $t->index('service_id');
                    });
                }
            }

            // 3) (Opcional) FKs si no estaban — solo si existen las tablas referenciadas
            if (Schema::hasTable('profiles')) {
                try {
                    Schema::table('profile_service', function (Blueprint $t) {
                        $t->foreign('profile_id')->references('id')->on('profiles')->cascadeOnDelete();
                    });
                } catch (\Throwable $e) { /* ya existe o no aplica */ }
            }
            if (Schema::hasTable('services')) {
                try {
                    Schema::table('profile_service', function (Blueprint $t) {
                        $t->foreign('service_id')->references('id')->on('services')->cascadeOnDelete();
                    });
                } catch (\Throwable $e) { /* ya existe o no aplica */ }
            }
        } else {
            // Si la tabla no existe, la creamos con id desde el inicio
            Schema::create('profile_service', function (Blueprint $t) {
                $t->id();
                $t->foreignId('profile_id');
                $t->foreignId('service_id');
                $t->unique(['profile_id', 'service_id'], 'profile_service_profile_id_service_id_unique');
                $t->index('profile_id');
                $t->index('service_id');
            });

            if (Schema::hasTable('profiles')) {
                Schema::table('profile_service', function (Blueprint $t) {
                    $t->foreign('profile_id')->references('id')->on('profiles')->cascadeOnDelete();
                });
            }
            if (Schema::hasTable('services')) {
                Schema::table('profile_service', function (Blueprint $t) {
                    $t->foreign('service_id')->references('id')->on('services')->cascadeOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        // No tocamos la unicidad ni las FKs; solo intentamos revertir el id si fuese necesario.
        if (Schema::hasTable('profile_service') && Schema::hasColumn('profile_service', 'id')) {
            if (Schema::getConnection()->getDriverName() === 'pgsql') {
                // Quitar PK en id y luego la columna (por seguridad, podés dejar vacío el down)
                try {
                    DB::statement('ALTER TABLE profile_service DROP CONSTRAINT IF EXISTS profile_service_pkey');
                    DB::statement('ALTER TABLE profile_service DROP COLUMN IF EXISTS id');
                } catch (\Throwable $e) { /* noop */ }
            } else {
                try {
                    Schema::table('profile_service', function (Blueprint $t) {
                        $t->dropColumn('id');
                    });
                } catch (\Throwable $e) { /* noop */ }
            }
        }
    }
};
