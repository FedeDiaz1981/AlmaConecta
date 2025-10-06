<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Crear specialties solo si no existe
        if (! Schema::hasTable('specialties')) {
            Schema::create('specialties', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        // Agregar specialty_id a profiles solo si no existe
        if (Schema::hasTable('profiles') && ! Schema::hasColumn('profiles', 'specialty_id')) {
            Schema::table('profiles', function (Blueprint $table) {
                // foreignId es portable (no usa unsigned en Postgres)
                $table->foreignId('specialty_id')
                      ->nullable()
                      ->index();
                // Si querés FK y tu DB la soporta, descomentá la línea de abajo:
                // $table->foreign('specialty_id')->references('id')->on('specialties')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('profiles') && Schema::hasColumn('profiles', 'specialty_id')) {
            Schema::table('profiles', function (Blueprint $table) {
                // Si llegaste a crear la FK, primero dropeala:
                // $table->dropForeign(['specialty_id']);
                $table->dropIndex(['specialty_id']);
                $table->dropColumn('specialty_id');
            });
        }

        Schema::dropIfExists('specialties');
    }
};
