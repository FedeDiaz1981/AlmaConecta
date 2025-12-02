<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContactFieldsToProfilesTable extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Ajustá los tipos/campos si ya los tenías distintos
            if (!Schema::hasColumn('profiles', 'whatsapp')) {
                $table->string('whatsapp')->nullable()->after('status');
            }

            if (!Schema::hasColumn('profiles', 'contact_email')) {
                $table->string('contact_email')->nullable()->after('whatsapp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (Schema::hasColumn('profiles', 'whatsapp')) {
                $table->dropColumn('whatsapp');
            }

            if (Schema::hasColumn('profiles', 'contact_email')) {
                $table->dropColumn('contact_email');
            }
        });
    }
}
