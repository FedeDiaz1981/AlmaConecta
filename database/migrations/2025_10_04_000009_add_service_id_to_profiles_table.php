<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Agregar columna si falta
        if (!Schema::hasColumn('profiles', 'service_id')) {
            Schema::table('profiles', function (Blueprint $t) {
                $t->unsignedBigInteger('service_id')->nullable()->after('template_key');
            });
        }

        // 2) Rellenar desde profile_service SIN depender de una columna "id"
        if (Schema::hasTable('profile_service') && Schema::hasColumn('profiles', 'service_id')) {
            $driver = Schema::getConnection()->getDriverName();

            if (in_array($driver, ['pgsql', 'mysql'])) {
                // Toma el menor service_id por perfil (elige 1 si hay varios)
                DB::statement("
                    UPDATE profiles p
                    SET service_id = sub.service_id
                    FROM (
                        SELECT profile_id, MIN(service_id) AS service_id
                        FROM profile_service
                        GROUP BY profile_id
                    ) AS sub
                    WHERE p.id = sub.profile_id
                      AND p.service_id IS NULL
                ");
            } else {
                // Fallback genérico en PHP (no usa orderBy('id'))
                DB::table('profile_service')
                    ->select('profile_id', DB::raw('MIN(service_id) as service_id'))
                    ->groupBy('profile_id')
                    ->orderBy('profile_id')
                    ->chunk(1000, function ($rows) {
                        foreach ($rows as $row) {
                            DB::table('profiles')
                              ->where('id', $row->profile_id)
                              ->whereNull('service_id')
                              ->update(['service_id' => $row->service_id]);
                        }
                    });
            }
        }

        // 3) FK opcional a services (solo si existe la tabla)
        if (Schema::hasTable('services') && Schema::hasColumn('profiles', 'service_id')) {
            if (Schema::getConnection()->getDriverName() === 'pgsql') {
                // Evita duplicar la FK en Postgres
                DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'profiles_service_id_foreign'
    ) THEN
        ALTER TABLE profiles
        ADD CONSTRAINT profiles_service_id_foreign
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL;
    END IF;
END$$;
SQL);
            } else {
                // Otros drivers: intenta crearla; si ya existe, ignora el error
                try {
                    Schema::table('profiles', function (Blueprint $t) {
                        $t->foreign('service_id', 'profiles_service_id_foreign')
                          ->references('id')->on('services')
                          ->nullOnDelete();
                    });
                } catch (\Throwable $e) { /* ya existe */ }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('profiles', 'service_id')) {
            if (Schema::getConnection()->getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE profiles DROP CONSTRAINT IF EXISTS profiles_service_id_foreign');
            } else {
                try {
                    Schema::table('profiles', function (Blueprint $t) {
                        $t->dropForeign('profiles_service_id_foreign');
                    });
                } catch (\Throwable $e) { /* puede no existir */ }
            }

            Schema::table('profiles', function (Blueprint $t) {
                $t->dropColumn('service_id');
            });
        }
    }
};
