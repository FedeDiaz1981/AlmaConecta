<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Si NO existe la tabla, la creamos con id y los índices/unique.
        if (!Schema::hasTable('profile_service')) {
            Schema::create('profile_service', function (Blueprint $t) {
                $t->bigIncrements('id');
                // columnas (sin FKs por si aún no existen las tablas referenciadas)
                $t->unsignedBigInteger('profile_id');
                $t->unsignedBigInteger('service_id');

                // índices y unique
                $t->index('profile_id', 'profile_service_profile_id_index');
                $t->index('service_id', 'profile_service_service_id_index');
                $t->unique(['profile_id','service_id'], 'profile_service_profile_service_unique');
            });

            // Si ya existen las tablas referenciadas, agregamos FKs ahora.
            if (Schema::hasTable('profiles') && Schema::hasTable('services')) {
                Schema::table('profile_service', function (Blueprint $t) {
                    $t->foreign('profile_id', 'profile_service_profile_id_foreign')
                      ->references('id')->on('profiles')->cascadeOnDelete();
                    $t->foreign('service_id', 'profile_service_service_id_foreign')
                      ->references('id')->on('services')->cascadeOnDelete();
                });
            }

            return;
        }

        // Si la tabla YA existe, aseguramos que tenga 'id' y los índices/unique.
        if (!Schema::hasColumn('profile_service', 'id')) {
            // Quitar PK compuesta si la hubiera
            DB::statement('ALTER TABLE profile_service DROP CONSTRAINT IF EXISTS profile_service_pkey');
            // Agregar columna id autoincremental como PK (Postgres)
            DB::statement('ALTER TABLE profile_service ADD COLUMN id BIGSERIAL PRIMARY KEY');
        }

        // Crear índices/unique si faltan (idempotente)
        DB::statement('CREATE INDEX IF NOT EXISTS profile_service_profile_id_index ON profile_service (profile_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS profile_service_service_id_index ON profile_service (service_id)');
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS profile_service_profile_service_unique ON profile_service (profile_id, service_id)');

        // Agregar FKs sólo si existen tablas destino y si aún no están las constraints
        if (Schema::hasTable('profiles') && Schema::hasTable('services')) {
            if (Schema::getConnection()->getDriverName() === 'pgsql') {
                // Postgres: chequear catálogo antes de crear constraints
                DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'profile_service_profile_id_foreign'
    ) THEN
        ALTER TABLE profile_service
        ADD CONSTRAINT profile_service_profile_id_foreign
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'profile_service_service_id_foreign'
    ) THEN
        ALTER TABLE profile_service
        ADD CONSTRAINT profile_service_service_id_foreign
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE;
    END IF;
END$$;
SQL);
            } else {
                // Otros drivers (MySQL, etc.) – SchemaBuilder estándar (omite si ya existen)
                Schema::table('profile_service', function (Blueprint $t) {
                    if (!Schema::hasColumn('profile_service', 'profile_id')) return;
                    if (!Schema::hasColumn('profile_service', 'service_id')) return;

                    try {
                        $t->foreign('profile_id', 'profile_service_profile_id_foreign')
                          ->references('id')->on('profiles')->cascadeOnDelete();
                    } catch (\Throwable $e) { /* ya existe */ }

                    try {
                        $t->foreign('service_id', 'profile_service_service_id_foreign')
                          ->references('id')->on('services')->cascadeOnDelete();
                    } catch (\Throwable $e) { /* ya existe */ }
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_service');
    }
};
