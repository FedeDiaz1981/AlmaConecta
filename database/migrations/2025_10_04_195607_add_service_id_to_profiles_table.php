<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('profiles', 'service_id')) {
                // nullable y FK; en sqlite funciona, si no querés FK podés quitar ->constrained()
                $table->foreignId('service_id')
                      ->nullable()
                      ->after('slug')
                      ->constrained()
                      ->nullOnDelete();
            }
        });

        // Opcional: backfill si tenías la tabla pivote profile_service
        if (Schema::hasTable('profile_service') && Schema::hasColumn('profiles', 'service_id')) {
            $pairs = DB::table('profile_service')->select('profile_id','service_id')->orderBy('id')->get();
            foreach ($pairs as $row) {
                DB::table('profiles')
                  ->where('id', $row->profile_id)
                  ->whereNull('service_id')
                  ->update(['service_id' => $row->service_id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (Schema::hasColumn('profiles', 'service_id')) {
                // Para DBs que no soportan dropConstrainedForeignId:
                if (Schema::hasColumn('profiles', 'service_id')) {
                    try { $table->dropForeign(['service_id']); } catch (\Throwable $e) {}
                }
                $table->dropColumn('service_id');
            }
        });
    }
};
