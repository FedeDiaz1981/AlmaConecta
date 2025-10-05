<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('edits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $t->json('payload');
            $t->enum('status',['pending','approved','rejected'])->default('pending');
            $t->foreignId('reviewed_by')->nullable()->constrained('users');
            $t->timestamp('reviewed_at')->nullable();
            $t->text('reason')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('edits'); }
};
