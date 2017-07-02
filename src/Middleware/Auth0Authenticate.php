<?php
namespace Auth0\Lumen\Middleware;

use Closure;
use Auth0\Lumen\Repository\Auth0UserRepository;
use Illuminate\Contracts\Auth\Factory as Auth;

class Auth0Authenticate
{
    protected $user_repository;

    protected $auth;

    protected $service;

    public function __construct(Auth $auth);
    {
        $this->auth = $auth;
        $this->user_repository = app()->make(Auth0UserRepository::class);
        $this->service = app()->make('auth0');
    }

    public function handle($request, Closure $next, $guard = null)
    {
        $profile = $this->service->getUser();
        if (empty($profile)) {
            return response('Unauthorized', 401);
        }

        $auth0_user = $this->userRepository->getUserByUserInfo($profile);
        $this->auth->viaRequest('api'. function() use ($auth0_user) {
            return $auth0_user;
        });
        return $next($request);
    }
}
