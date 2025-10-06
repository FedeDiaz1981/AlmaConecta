<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('provider');
            }
            if (!Schema::hasColumn('users', 'account_status')) {
                $table->string('account_status')->default('pending'); // pending|active|rejected|suspended
            }
            if (!Schema::hasColumn('users', 'approved_at'))   $table->timestamp('approved_at')->nullable();
            if (!Schema::hasColumn('users', 'rejected_at'))   $table->timestamp('rejected_at')->nullable();
            if (!Schema::hasColumn('users', 'suspended_at'))  $table->timestamp('suspended_at')->nullable();
            if (!Schema::hasColumn('users', 'activated_at'))  $table->timestamp('activated_at')->nullable();
            if (!Schema::hasColumn('users', 'reject_reason')) $table->string('reject_reason')->nullable();
            if (!Schema::hasColumn('users', 'suspend_reason'))$table->string('suspend_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // En SQLite dropColumn ya funciona en Laravel moderno; si no, podés dejar vacío el down.
            foreach ([
                'role','account_status','approved_at','rejected_at',
                'suspended_at','activated_at','reject_reason','suspend_reason'
            ] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
