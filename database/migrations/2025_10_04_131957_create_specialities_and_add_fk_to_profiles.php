<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('specialties', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::table('profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('profiles', 'specialty_id')) {
                $table->unsignedBigInteger('specialty_id')->nullable()->after('template_key');
                // Si las FKs te rompen en SQLite, dejá comentada la línea de abajo
                // $table->foreign('specialty_id')->references('id')->on('specialties')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (Schema::hasColumn('profiles', 'specialty_id')) {
                // $table->dropForeign(['specialty_id']);
                $table->dropColumn('specialty_id');
            }
        });
        Schema::dropIfExists('specialties');
    }
};
