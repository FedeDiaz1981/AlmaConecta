<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'document_type')) {
                $table->string('document_type', 30)->nullable();
            }
            if (!Schema::hasColumn('users', 'document_number')) {
                $table->string('document_number', 50)->nullable();
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('users', 'document_number')) {
                $table->dropColumn('document_number');
            }
            if (Schema::hasColumn('users', 'document_type')) {
                $table->dropColumn('document_type');
            }
        });
    }
};
