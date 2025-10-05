<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Definimos el gate acá porque este provider SIEMPRE se carga.
        if (! Gate::has('admin')) {
            Gate::define('admin', function (User $user) {
                $status = $user->account_status ?? 'active';
                return $user->role === 'admin' && $status !== 'suspended';
            });
        }

        if (! Gate::has('provider-active')) {
            Gate::define('provider-active', function (User $user) {
                $status = $user->account_status ?? 'active';
                return $user->role === 'provider' && $status === 'active';
            });
        }

        if (! Gate::has('account-active')) {
            Gate::define('account-active', function (User $user) {
                $status = $user->account_status ?? 'active';
                return $status === 'active';
            });
        }

        // Opcional: superatajo para admins
        // Gate::before(fn(User $u, string $ability) => $u->role === 'admin' ? true : null);
    }
}
