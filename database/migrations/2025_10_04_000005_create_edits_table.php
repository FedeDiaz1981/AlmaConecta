<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('edits', function (Blueprint $t) {
            $t->id();

            // Depende de 'profiles' (asegurate de que la migración de profiles corra antes)
            $t->foreignId('profile_id')
              ->constrained('profiles') // references id on profiles
              ->cascadeOnDelete();

            $t->json('payload');
            $t->enum('status', ['pending','approved','rejected'])->default('pending');

            // columna + FK explícita a users; null si se borra el revisor
            $t->foreignId('reviewed_by')->nullable();
            $t->foreign('reviewed_by')
              ->references('id')->on('users')
              ->nullOnDelete();

            $t->timestamp('reviewed_at')->nullable();
            $t->text('reason')->nullable();

            $t->timestamps();

            // índices útiles
            $t->index('status');
            $t->index('reviewed_by');
        });
    }

    public function down(): void {
        Schema::dropIfExists('edits');
    }
};
