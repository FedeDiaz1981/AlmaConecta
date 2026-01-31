<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Profile;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        // Ajustá el criterio según tu esquema real:
        // - role = 'provider'
        // - account_status = 'active'
        // - approved_at != null
        $providers = User::query()
            ->where('role', 'provider')
            ->where('account_status', 'active')
            ->get(['id', 'name']);

        foreach ($providers as $u) {
            Profile::firstOrCreate(
                ['user_id' => $u->id],
                [
                    'display_name' => $u->name,
                    'slug' => Str::slug($u->name) . '-' . $u->id,
                    'status' => 'approved', // o pending
                    'approved_at' => now(),
                    'country' => 'AR',
                ]
            );
        }
    }

    public function down(): void
    {
        // No hacemos rollback automático porque podrías borrar perfiles reales.
        // Si querés, acá podrías borrar solo los que estén vacíos.
    }
};
