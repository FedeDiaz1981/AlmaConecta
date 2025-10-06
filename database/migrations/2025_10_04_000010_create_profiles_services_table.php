<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('profile_service')) {
            Schema::create('profile_service', function (Blueprint $t) {
                $t->id(); // para evitar "ORDER BY id" en consultas del app
                $t->foreignId('profile_id');
                $t->foreignId('service_id');
                $t->unique(['profile_id', 'service_id']);
                // opcional: timestamps si querés auditar altas/bajas
                // $t->timestamps();

                $t->index('profile_id');
                $t->index('service_id');
            });

            // Agregar FKs solo si las tablas existen (evita fallos en el primer deploy)
            if (Schema::hasTable('profiles')) {
                Schema::table('profile_service', function (Blueprint $t) {
                    $t->foreign('profile_id')
                      ->references('id')->on('profiles')
                      ->cascadeOnDelete();
                });
            }

            if (Schema::hasTable('services')) {
                Schema::table('profile_service', function (Blueprint $t) {
                    $t->foreign('service_id')
                      ->references('id')->on('services')
                      ->cascadeOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        // Si creaste FKs, se dropean automáticamente al dropear la tabla en PG.
        Schema::dropIfExists('profile_service');
    }
};
