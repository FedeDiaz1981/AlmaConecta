<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Si ya existe por algo, la tiramos y la creamos bien
        Schema::dropIfExists('profile_service');

        Schema::create('profile_service', function (Blueprint $table) {
            // en un pivot ni siquiera hace falta id, pero si querés:
            // $table->id();

            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['profile_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_service');
    }
};
