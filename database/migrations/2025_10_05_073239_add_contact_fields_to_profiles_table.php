// database/migrations/2025_10_04_000001_add_contact_fields_to_profiles_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('profiles', 'whatsapp')) {
                $table->string('whatsapp', 30)->nullable()->after('video_url');
            }
            if (!Schema::hasColumn('profiles', 'contact_email')) {
                $table->string('contact_email', 255)->nullable()->after('whatsapp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (Schema::hasColumn('profiles', 'whatsapp')) $table->dropColumn('whatsapp');
            if (Schema::hasColumn('profiles', 'contact_email')) $table->dropColumn('contact_email');
        });
    }
};
