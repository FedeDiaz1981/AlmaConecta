<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // ---- PROFILES ----
        Schema::create('profiles', function (Blueprint $t) {
            $t->id();

            // 1:1 con users (borra el perfil si se borra el user)
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

            // Postgres: numeric
            $t->decimal('lat', 10, 7)->nullable();
            $t->decimal('lng', 10, 7)->nullable();

            $t->enum('template_key', ['a', 'b'])->default('a');
            $t->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();

            $t->timestamp('approved_at')->nullable();
            $t->timestamps();
        });

        // FULLTEXT solo para MySQL (evita error en Postgres)
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                'ALTER TABLE profiles ADD FULLTEXT fulltext_profile (display_name, about)'
            );
        }

        // ---- PIVOTE profile_service (requiere que "services" ya exista) ----
        Schema::create('profile_service', function (Blueprint $t) {
            $t->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $t->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $t->primary(['profile_id', 'service_id']);
        });
    }

    public function down(): void
    {
        // Dropear primero la pivote por las FKs
        Schema::dropIfExists('profile_service');
        Schema::dropIfExists('profiles');
    }
};
