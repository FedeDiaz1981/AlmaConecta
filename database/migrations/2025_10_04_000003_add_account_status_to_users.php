<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $t) {
            if (!Schema::hasColumn('users','role')) {
                $t->string('role')->default('provider');
            }
            if (!Schema::hasColumn('users','account_status')) {
                $t->enum('account_status', ['pending','active','rejected','suspended'])->default('pending');
            }
            if (!Schema::hasColumn('users','approved_at')) {
                $t->timestamp('approved_at')->nullable();
            }
            if (!Schema::hasColumn('users','suspended_at')) {
                $t->timestamp('suspended_at')->nullable();
            }
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $t) {
            if (Schema::hasColumn('users','suspended_at')) $t->dropColumn('suspended_at');
            if (Schema::hasColumn('users','approved_at')) $t->dropColumn('approved_at');
            if (Schema::hasColumn('users','account_status')) $t->dropColumn('account_status');
            // role lo podés dejar si ya lo usás
        });
    }
};
