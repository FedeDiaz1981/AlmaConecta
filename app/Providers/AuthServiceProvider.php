<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot(): void
    {
        // --- GATES ---

        // Admin: rol admin y no suspendido
        Gate::define('admin', function (User $user) {
            $status = $user->account_status ?? 'active';
            return ($user->role === 'admin') && ($status !== 'suspended');
        });

        // Provider activo
        Gate::define('provider-active', function (User $user) {
            $status = $user->account_status ?? 'active';
            return ($user->role === 'provider') && ($status === 'active');
        });

        // Cuenta activa (genérico)
        Gate::define('account-active', function (User $user) {
            $status = $user->account_status ?? 'active';
            return $status === 'active';
        });

        // Pase previo: cualquier admin pasa cualquier gate
        Gate::before(fn(User $u, string $ability) => $u->role === 'admin' ? true : null);
    }
}
