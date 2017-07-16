<?php
namespace Auth0\Lumen;

use Illuminate\Support\ServiceProvider;
use Auth0\SDK\API\Helpers\ApiClient;
use Auth0\SDK\API\Helpers\InformationHeaders;

class Auth0ServiceProvider extends ServiceProvider
{
    const SDK_VERSION = '1.0.0';

    public function register()
    {
        $this->app->singleton('auth0', function() {
            return new Auth0Service();
        });

        // When Lumen logs out, logout the auth0 SDK trough the service
        \Event::listen('auth.logout', function () {
            app()->make('auth0')->logout();
        });
        \Event::listen('user.logout', function () {
            app()->make('auth0')->logout();
        });
        \Event::listen('Illuminate\Auth\Events\Logout', function () {
            app()->make('auth0')->logout();
        });
    }

    public function boot()
    {
        app()->make('auth')->provider('auth0', function($app) {
            return $app->make(Auth0UserProvider::class);
        });

        $oldInfoHeaders = ApiClient::getInfoHeadersData();

        if ($oldInfoHeaders) {
            $infoHeaders = InformationHeaders::Extend($oldInfoHeaders);
            $infoHeaders->setEnvironment('Lumen', app()->version());
            $infoHeaders->setPackage('lumen-auth0', self::SDK_VERSION);

            ApiClient::setInfoHeadersData($infoHeaders);
        }
        app()->routeMiddleware([
            'auth0' => \Auth0\Lumen\Middleware\Auth0Authenticate::class,
            'auth0.jwt' => \Auth0\Lumen\Middleware\Auth0JWT::class
        ]);
    }
}
