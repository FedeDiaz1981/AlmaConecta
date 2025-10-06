<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $t) {
            $t->id();

            // Relación 1:1 con users; elimina el perfil si se borra el usuario
            $t->foreignId('user_id')
              ->constrained('users')
              ->cascadeOnDelete()
              ->unique();

            $t->string('display_name');
            $t->string('slug')->unique();
            $t->text('about')->nullable();

            $t->boolean('mode_presential')->default(true);
            $t->boolean('mode_remote')->default(false);

            $t->string('country', 2)->default('AR');
            $t->string('state')->nullable();
            $t->string('city')->nullable();
            $t->string('address')->nullable();

            // Coordenadas (NUMERIC en Postgres)
            $t->decimal('lat', 10, 7)->nullable();
            $t->decimal('lng', 10, 7)->nullable();

            $t->enum('template_key', ['a', 'b'])->default('a');
            $t->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();

            $t->timestamp('approved_at')->nullable();
            $t->timestamps();
        });

        // FULLTEXT solo para MySQL (evita errores en Postgres)
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement(
                'ALTER TABLE profiles ADD FULLTEXT fulltext_profile (display_name, about)'
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
