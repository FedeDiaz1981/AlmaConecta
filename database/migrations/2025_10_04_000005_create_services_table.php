<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('services')) {
            Schema::create('services', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('slug')->unique();
                $t->timestamps();
            });
        }

        // Si alguna vez usás MySQL y querés FULLTEXT:
        // if (\DB::getDriverName() === 'mysql') {
        //     \DB::statement('ALTER TABLE services ADD FULLTEXT fulltext_services (name)');
        // }
    }
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
