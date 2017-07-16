<?php
namespace Auth0\Lumen\Middleware;

use Closure;
use Auth0\Lumen\Repository\Auth0UserRepository;
use Auth0\SDK\Exception\CoreException;
use Auth0\SDK\Exception\InvalidTokenException;
use Illuminate\Contracts\Auth\Factory as Auth;

class Auth0JWT
{
    protected $userRepository;

    protected $auth;

    /**
     * Auth0JWTMiddleware constructor.
     *
     * @param Auth0UserRepository $userRepository
     */
    public function __construct(Auth $auth)
    {
        $this->userRepository = app()->make(Auth0UserRepository::class);
        $this->auth = $auth;
    }

    /**
     * @param $request
     *
     * @return string
     */
    protected function getToken($request)
    {
        // Get the encrypted user JWT
        $authorizationHeader = $request->header('Authorization');

        return trim(str_replace('Bearer ', '', $authorizationHeader));
    }

    /**
     * @param $token
     *
     * @return bool
     */
    protected function validateToken($token)
    {
        return $token !== '';
    }

    /**
     * @param $request
     * @param Closure $next
     *
     * @return mixed
     */
     public function handle($request, Closure $next)
     {
         $auth0 = app()->make('auth0');
         $token = $this->getToken($request);

         if (!$this->validateToken($token)) {
             return response('Unauthorized user', 401);
         }

         $authorize_token = $auth0->authorizeAccessToken($token);

         if ($token) {
             try {
                 $jwtUser = $auth0->decodeJWT($token);
             } catch (CoreException $e) {
                 return response('Unauthorized user', 401);
             } catch (InvalidTokenException $e) {
                 return response('Unauthorized user', 401);
             }

             // if it does not represent a valid user, return a HTTP 401
             $user = $this->userRepository->getUserByDecodedJWT($jwtUser);

             if (!$user) {
                 return response('Unauthorized user', 401);
             }

             // lets log the user in so it is accessible
             $this->auth->viaRequest('api', function() use ($user) {
                 return $user;
             });
         }
         // continue the execution
         return $next($request);
     }
}
