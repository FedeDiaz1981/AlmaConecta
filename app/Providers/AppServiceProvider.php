<?php

namespace App\Providers;

use App\Database\Connectors\NeonPostgresConnector;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('db.connector.pgsql', function () {
            return new NeonPostgresConnector;
        });
    }

    public function boot(): void
    {
        // Asegura CA bundle para TLS en SMTP (evita errores de cafile en Windows)
        if ($cafile = env('MAIL_CAFILE')) {
            @ini_set('openssl.cafile', $cafile);
            @ini_set('curl.cainfo', $cafile);
        }

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
