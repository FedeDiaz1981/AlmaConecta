<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Si la tabla ya existe (de un deploy previo), no la volvemos a crear
        if (Schema::hasTable('cache')) {
            // Opcional: asegurar columnas mínimas si tuvieras una tabla creada a mano
            Schema::table('cache', function (Blueprint $t) {
                if (! Schema::hasColumn('cache', 'key')) {
                    $t->string('key');
                }
                if (! Schema::hasColumn('cache', 'value')) {
                    $t->text('value');
                }
                if (! Schema::hasColumn('cache', 'expiration')) {
                    $t->integer('expiration');
                }
            });

            return;
        }

        Schema::create('cache', function (Blueprint $t) {
            // Esquema por defecto de Laravel
            $t->string('key')->primary();
            $t->text('value');
            $t->integer('expiration')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache');
    }
};
