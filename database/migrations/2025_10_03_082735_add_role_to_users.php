<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends \Illuminate\Database\Migrations\Migration {
    public function up(): void {
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $t) {
                $t->string('role')->default('provider');
            });
        }
    }
    public function down(): void {
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $t) {
                $t->dropColumn('role');
            });
        }
    }
};
