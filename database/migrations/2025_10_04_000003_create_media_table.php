<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Si ya existe (quedó de un intento previo), no la volvemos a crear
        if (Schema::hasTable('media')) {
            return;
        }

        Schema::create('media', function (Blueprint $t) {
            $t->id();

            // FK a profiles; se borran los media si se borra el perfil
            $t->foreignId('profile_id')
              ->constrained('profiles')
              ->cascadeOnDelete();

            $t->enum('type', ['image', 'video']);
            $t->string('url');
            $t->unsignedInteger('position')->default(0);

            $t->timestamps();

            // Índices útiles
            $t->index(['profile_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
