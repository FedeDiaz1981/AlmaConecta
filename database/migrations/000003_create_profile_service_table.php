<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('profile_service', function (Blueprint $t) {
            $t->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $t->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $t->primary(['profile_id','service_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('profile_service'); }
};
