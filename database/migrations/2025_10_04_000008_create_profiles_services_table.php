<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Si NO existe la tabla, crearla ya normalizada
        if (!Schema::hasTable('profile_service')) {
            Schema::create('profile_service', function (Blueprint $t) {
                $t->bigIncrements('id');

                // columnas
                $t->unsignedBigInteger('profile_id');
                $t->unsignedBigInteger('service_id');

                // índices y unique
                $t->index('profile_id', 'profile_service_profile_id_index');
                $t->index('service_id', 'profile_service_service_id_index');
                $t->unique(['profile_id','service_id'], 'profile_service_profile_service_unique');
            });

            // Agregar FKs si existen tablas destino
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

        // 2) Si YA existe, normalizarla para que tenga 'id' y esté indexada
        //    (ruta de upgrade desde pivot sin 'id')
        // Quitar posible PK compuesta
        try {
            DB::statement('ALTER TABLE profile_service DROP CONSTRAINT IF EXISTS profile_service_pkey');
        } catch (\Throwable $e) {
            // ignorar
        }

        // Agregar columna 'id' si falta
        if (!Schema::hasColumn('profile_service', 'id')) {
            if (Schema::getConnection()->getDriverName() === 'pgsql') {
                // Postgres: agregar columna, crear secuencia, backfill y PK
                DB::unprepared(<<<'SQL'
DO $$
BEGIN
    -- 1) Agregar columna si no existe (NULL por ahora)
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_name='profile_service' AND column_name='id'
    ) THEN
        ALTER TABLE profile_service ADD COLUMN id BIGINT;
    END IF;

    -- 2) Crear secuencia si no existe
    IF NOT EXISTS (
        SELECT 1 FROM pg_class WHERE relkind='S' AND relname='profile_service_id_seq'
    ) THEN
        CREATE SEQUENCE profile_service_id_seq START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1;
    END IF;

    -- 3) Backfill de IDs faltantes
    UPDATE profile_service
       SET id = nextval('profile_service_id_seq')
     WHERE id IS NULL;

    -- 4) Default + NOT NULL
    ALTER TABLE profile_service
        ALTER COLUMN id SET DEFAULT nextval('profile_service_id_seq'),
        ALTER COLUMN id SET NOT NULL;

    -- 5) Definir PK en 'id' si no existe
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conrelid = 'profile_service'::regclass AND contype = 'p'
    ) THEN
        ALTER TABLE profile_service ADD CONSTRAINT profile_service_pkey PRIMARY KEY (id);
    END IF;
END$$;
SQL);
            } else {
                // MySQL/MariaDB
                // Nota: en MySQL no se puede tener dos PK; asumimos que ya se dropeó la compuesta arriba.
                DB::statement('ALTER TABLE profile_service ADD COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');
            }
        }

        // Índices y unique idempotentes
        DB::statement('CREATE INDEX IF NOT EXISTS profile_service_profile_id_index ON profile_service (profile_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS profile_service_service_id_index ON profile_service (service_id)');

        // Unique compuesto (si ya existía con otro nombre, este crea uno nuevo sin romper)
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("
                DO $$
                BEGIN
                    IF NOT EXISTS (
                        SELECT 1
                        FROM pg_indexes
                        WHERE schemaname = 'public'
                          AND indexname = 'profile_service_profile_service_unique'
                    ) THEN
                        CREATE UNIQUE INDEX profile_service_profile_service_unique
                            ON profile_service (profile_id, service_id);
                    END IF;
                END$$;
            ");
        } else {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS profile_service_profile_service_unique ON profile_service (profile_id, service_id)');
        }

        // FKs: crear solo si existen tablas destino y aún no están las constraints
        if (Schema::hasTable('profiles') && Schema::hasTable('services')) {
            if (Schema::getConnection()->getDriverName() === 'pgsql') {
                DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'profile_service_profile_id_foreign') THEN
        ALTER TABLE profile_service
        ADD CONSTRAINT profile_service_profile_id_foreign
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'profile_service_service_id_foreign') THEN
        ALTER TABLE profile_service
        ADD CONSTRAINT profile_service_service_id_foreign
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE;
    END IF;
END$$;
SQL);
            } else {
                // Otros drivers
                Schema::table('profile_service', function (Blueprint $t) {
                    try {
                        $t->foreign('profile_id', 'profile_service_profile_id_foreign')
                          ->references('id')->on('profiles')->cascadeOnDelete();
                    } catch (\Throwable $e) {}

                    try {
                        $t->foreign('service_id', 'profile_service_service_id_foreign')
                          ->references('id')->on('services')->cascadeOnDelete();
                    } catch (\Throwable $e) {}
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_service');
    }
};
