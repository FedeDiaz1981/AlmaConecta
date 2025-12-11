<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // contador de visualizaciones, arranca en 0
            $table->unsignedBigInteger('views_count')
                  ->default(0)
                  ->after('contact_email'); // o después del campo que prefieras
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('views_count');
        });
    }
};
