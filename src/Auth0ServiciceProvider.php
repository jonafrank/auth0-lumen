<?php
namespace Auth0\Lumen;

use Illuminate\Support\ServiceProvider;

class Auth0ServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('auth0', function() {
            return new Auth0Service();
        });
    }
}
