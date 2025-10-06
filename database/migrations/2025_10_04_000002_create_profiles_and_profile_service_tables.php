<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) PROFILES ---------------------------------------------------------
        if (!Schema::hasTable('profiles')) {
            Schema::create('profiles', function (Blueprint $t) {
                $t->id();

                // Relación 1:1 con users; si el user se borra, se borra el perfil
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

                $t->decimal('lat', 10, 7)->nullable();
                $t->decimal('lng', 10, 7)->nullable();

                $t->enum('template_key', ['a', 'b'])->default('a');
                $t->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();

                $t->timestamp('approved_at')->nullable();
                $t->timestamps();
            });

            // Solo agregar FULLTEXT en MySQL (evita error en Postgres)
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE profiles ADD FULLTEXT fulltext_profile (display_name, about)');
            }
        }

        // 2) PIVOT profile_service -------------------------------------------
        if (!Schema::hasTable('profile_service')) {
            Schema::create('profile_service', function (Blueprint $t) {
                // Usamos columnas explícitas + foreign() para evitar fallos si aún no existe alguna FK
                $t->unsignedBigInteger('profile_id');
                $t->unsignedBigInteger('service_id');

                $t->primary(['profile_id','service_id']);

                $t->foreign('profile_id')
                  ->references('id')->on('profiles')
                  ->cascadeOnDelete();

                $t->foreign('service_id')
                  ->references('id')->on('services')
                  ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Bajar en orden inverso
        Schema::dropIfExists('profile_service');
        Schema::dropIfExists('profiles');
    }
};
