<?php

namespace App\Providers;

use App\Providers\Keycloak\KeycloakGuard;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Tolérance sur les écarts d'horloge entre Keycloak et ce serveur.
        JWT::$leeway = 10;

        Auth::extend('keycloak', function ($app, $name, array $config) {
            return new KeycloakGuard(Auth::createUserProvider($config['provider']), $app->make('request'));
        });
    }
}
