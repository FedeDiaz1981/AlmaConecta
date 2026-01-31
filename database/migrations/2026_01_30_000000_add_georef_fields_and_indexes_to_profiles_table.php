<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function driver(): string
    {
        return Schema::getConnection()->getDriverName();
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = $this->driver();

        try {
            if ($driver === 'mysql') {
                $dbName = DB::getDatabaseName();
                $row = DB::selectOne(
                    'SELECT COUNT(1) AS c
                     FROM information_schema.statistics
                     WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                    [$dbName, $table, $indexName]
                );
                return (int)($row->c ?? 0) > 0;
            }

            if ($driver === 'pgsql') {
                $row = DB::selectOne(
                    "SELECT COUNT(1) AS c
                     FROM pg_indexes
                     WHERE schemaname = current_schema() AND tablename = ? AND indexname = ?",
                    [$table, $indexName]
                );
                return (int)($row->c ?? 0) > 0;
            }

            if ($driver === 'sqlite') {
                $row = DB::selectOne(
                    "SELECT COUNT(1) AS c
                     FROM sqlite_master
                     WHERE type='index' AND tbl_name = ? AND name = ?",
                    [$table, $indexName]
                );
                return (int)($row->c ?? 0) > 0;
            }
        } catch (\Throwable $e) {
            // Si no podemos inspeccionar, asumimos "no existe" y dejamos que el create sea el que falle/capture.
        }

        return false;
    }

    private function createIndexIfMissing(string $table, string $indexName, string $sql): void
    {
        try {
            if (!$this->indexExists($table, $indexName)) {
                DB::statement($sql);
            }
        } catch (\Throwable $e) {
            // No rompemos migración por un índice (puede existir con otro nombre o haber sido creado manualmente)
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        $driver = $this->driver();

        try {
            if (!$this->indexExists($table, $indexName)) return;

            if ($driver === 'mysql') {
                DB::statement("DROP INDEX `$indexName` ON `$table`");
                return;
            }

            if ($driver === 'pgsql') {
                DB::statement("DROP INDEX IF EXISTS \"$indexName\"");
                return;
            }

            if ($driver === 'sqlite') {
                DB::statement("DROP INDEX IF EXISTS \"$indexName\"");
                return;
            }
        } catch (\Throwable $e) {
            // noop
        }
    }

    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {

            // --- Columnas nuevas (si no existen) ---
            if (!Schema::hasColumn('profiles', 'province_id')) {
                $table->string('province_id', 50)->nullable();
            }
            if (!Schema::hasColumn('profiles', 'province_name')) {
                $table->string('province_name', 120)->nullable();
            }

            if (!Schema::hasColumn('profiles', 'city_id')) {
                $table->string('city_id', 80)->nullable();
            }
            if (!Schema::hasColumn('profiles', 'city_name')) {
                $table->string('city_name', 120)->nullable();
            }

            if (!Schema::hasColumn('profiles', 'address_extra')) {
                $table->string('address_extra', 120)->nullable();
            }
        });

        // --- Índices (defensivos, multi-driver) ---
        $driver = $this->driver();

        // city_id
        $this->createIndexIfMissing(
            'profiles',
            'profiles_city_id_idx',
            $driver === 'mysql'
                ? "CREATE INDEX `profiles_city_id_idx` ON `profiles` (`city_id`)"
                : "CREATE INDEX profiles_city_id_idx ON profiles (city_id)"
        );

        // province_id
        $this->createIndexIfMissing(
            'profiles',
            'profiles_province_id_idx',
            $driver === 'mysql'
                ? "CREATE INDEX `profiles_province_id_idx` ON `profiles` (`province_id`)"
                : "CREATE INDEX profiles_province_id_idx ON profiles (province_id)"
        );

        // status + city_id + mode_presential
        $this->createIndexIfMissing(
            'profiles',
            'profiles_status_city_presential_idx',
            $driver === 'mysql'
                ? "CREATE INDEX `profiles_status_city_presential_idx` ON `profiles` (`status`, `city_id`, `mode_presential`)"
                : "CREATE INDEX profiles_status_city_presential_idx ON profiles (status, city_id, mode_presential)"
        );

        // status + mode_remote
        $this->createIndexIfMissing(
            'profiles',
            'profiles_status_remote_idx',
            $driver === 'mysql'
                ? "CREATE INDEX `profiles_status_remote_idx` ON `profiles` (`status`, `mode_remote`)"
                : "CREATE INDEX profiles_status_remote_idx ON profiles (status, mode_remote)"
        );
    }

    public function down(): void
    {
        // Drop indexes primero (defensivo)
        $this->dropIndexIfExists('profiles', 'profiles_city_id_idx');
        $this->dropIndexIfExists('profiles', 'profiles_province_id_idx');
        $this->dropIndexIfExists('profiles', 'profiles_status_city_presential_idx');
        $this->dropIndexIfExists('profiles', 'profiles_status_remote_idx');

        Schema::table('profiles', function (Blueprint $table) {

            // Drop columnas después (si existen)
            $cols = [
                'province_id',
                'province_name',
                'city_id',
                'city_name',
                'address_extra',
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('profiles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
