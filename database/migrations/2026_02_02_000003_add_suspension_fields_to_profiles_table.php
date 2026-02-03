<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('profiles', 'is_suspended')) {
                $table->boolean('is_suspended')->default(false)->index();
            }
            if (!Schema::hasColumn('profiles', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (Schema::hasColumn('profiles', 'suspended_at')) {
                $table->dropColumn('suspended_at');
            }
            if (Schema::hasColumn('profiles', 'is_suspended')) {
                $table->dropColumn('is_suspended');
            }
        });
    }
};
