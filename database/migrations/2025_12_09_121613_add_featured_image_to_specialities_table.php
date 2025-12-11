<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La tabla REAL es "specialties"
        if (Schema::hasTable('specialties') && !Schema::hasColumn('specialties', 'featured_image_path')) {
            Schema::table('specialties', function (Blueprint $table) {
                $table->string('featured_image_path')->nullable()->after('is_featured');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('specialties') && Schema::hasColumn('specialties', 'featured_image_path')) {
            Schema::table('specialties', function (Blueprint $table) {
                $table->dropColumn('featured_image_path');
            });
        }
    }
};
