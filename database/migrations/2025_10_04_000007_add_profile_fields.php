<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // ya deberías tener specialty_id; si no, lo agrega
            if (!Schema::hasColumn('profiles','specialty_id')) {
                $table->unsignedBigInteger('specialty_id')->nullable()->after('template_key');
            }

            if (!Schema::hasColumn('profiles','coverage_area')) {
                $table->string('coverage_area')->nullable()->after('city'); // descripción corta de zona
            }
            if (!Schema::hasColumn('profiles','about')) {
                $table->text('about')->nullable(); // detalle con rich text
            }
            if (!Schema::hasColumn('profiles','photo_path')) {
                $table->string('photo_path')->nullable();
            }
            if (!Schema::hasColumn('profiles','video_url')) {
                $table->string('video_url')->nullable();
            }

            // geolocalización para cercanía
            if (!Schema::hasColumn('profiles','lat')) $table->decimal('lat', 10, 7)->nullable();
            if (!Schema::hasColumn('profiles','lng')) $table->decimal('lng', 10, 7)->nullable();

            // modalidad: usamos los dos flags ya existentes y los manejamos desde un select
            if (!Schema::hasColumn('profiles','mode_remote'))     $table->boolean('mode_remote')->default(false);
            if (!Schema::hasColumn('profiles','mode_presential')) $table->boolean('mode_presential')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            foreach (['specialty_id','coverage_area','about','photo_path','video_url','lat','lng','mode_remote','mode_presential'] as $col) {
                if (Schema::hasColumn('profiles', $col)) $table->dropColumn($col);
            }
        });
    }
};
