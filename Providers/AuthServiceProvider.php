<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot(): void
    {
        // --- GATES ---

        // Admin: rol admin y cuenta activa
        Gate::define('admin', function (User $user) {
            return ($user->role === 'admin') && (($user->account_status ?? 'inactive') === 'active');
        });

        // Provider activo
        Gate::define('provider-active', function (User $user) {
            $status = $user->account_status ?? 'inactive';
            return ($user->role === 'provider') && ($status === 'active');
        });

        // Cuenta activa (genérico)
        Gate::define('account-active', function (User $user) {
            return (($user->account_status ?? 'inactive') === 'active');
        });

        // (Opcional) Super-atajo: cualquier admin pasa cualquier Gate
        // Gate::before(fn (User $u, string $ability) => $u->role === 'admin' ? true : null);
    }
}
