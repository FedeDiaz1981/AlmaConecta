<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('media', function (Blueprint $t) {
            $t->id();
            $t->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $t->enum('type',['image','video']);
            $t->string('url');
            $t->unsignedInteger('position')->default(0);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('media'); }
};
