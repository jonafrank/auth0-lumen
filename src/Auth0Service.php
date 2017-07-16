<?php
namespace Auth0\Lumen;

use Auth0\SDK\API\Authentication;
use Auth0\SDK\Auth0;
use Auth0\SDK\JWTVerifier;
use Illuminate\Contracts\Container\BindingResolutionException;


class Auth0Service
{
    public $remember_user;
    protected $auth0_config;
    protected $auth_api;
    protected $ahut0;
    protected $apiuser;
    protected $on_login_cb = null;

    public function __construct()
    {
        $this->auth0_config = $this->loadConfig();
        $this->auth_api = new Authentication($this->auth0_config['domain'], $this->auth0_config['client_id']);
        $this->auth0 = new Auth0($this->auth0_config);
    }

    /**
     * As Lumen does not suport config files as laravel we load al the configuration
     * from dotenv file.
     *
     * @return Array
     */
    protected function loadConfig()
    {
        $config = [
            'domain'               => env('AUTH0_DOMAIN'),
            'client_id'            => env('AUTH0_CLIENT_ID'),
            'client_secret'        => env('AUTH0_CLIENT_SECRET'),
            'redirect_uri'         => env('AUTH0_REDIRECT_URI'),
            'persist_user'         => env('AUTH0_PERSIST_USER', true),
            'persist_access_token' => env('AUTH0_PERSIST_ACCES_TOKEN', false),
            'pesist_id_token'      => env('AUTH0_PESIST_ID_TOKEN', false),
            'api_identifier'       => env('AUTH0_API_IDENTIFIER', ''),
            'authorized_issuers'   => (env('AUTH0_AUTHORIZED_ISSUERS')) ? explode(',', env('AUTH0_AUTHORIZED_ISSUERS')) : [],
            'suported_algs'        => (env('AUTH0_SUPORTED_ALGS')) ? explode(',', env('AUTH0_SUPORTED_ALGS')) : []
        ];
        $config['store'] = new LumenSessionStore();
        return $config;
    }

    /**
     * Creates an instance of the Auth0 SDK using
     * the config set .env file and using a LumenSession
     * as a store mechanism.
     */
    private function getSDK()
    {
        return $this->auth0;
    }

    /**
     * Logs the user out from the SDK.
     */
    public function logout()
    {
        $this->getSDK()->logout();
    }

    /**
     * Redirects the user to the hosted login page
     */
    public function login($connection = null, $state = null, $aditional_params = [], $response_type = 'code')
    {
      $url = $this->authApi->get_authorize_link($response_type, $this->auth0_config['redirect_uri'], $connection, $state, $aditional_params);
      return redirect($url);
    }

    /**
     * If the user is logged in, returns the user information.
     *
     * @return array with the User info as described in https://docs.auth0.com/user-profile and the user access token
     */
    public function getUser()
    {
        // Get the user info from auth0
        $auth0 = $this->getSDK();
        $user = $auth0->getUser();

        if ($user === null) {
            return;
        }

        return [
            'profile'     => $user,
            'accessToken' => $auth0->getAccessToken(),
        ];
    }

    /**
     * Sets a callback to be called when the user is logged in.
     *
     * @param callback $cb A function that receives an auth0_user and receives a Lumen user
     */
    public function onLogin($cb)
    {
        $this->on_login_cb = $cb;
    }

    /**
     * @return bool
     */
    public function hasOnLogin()
    {
        return $this->on_login_cb !== null;
    }

    /**
     * @param $auth0_user
     *
     * @return mixed
     */
    public function callOnLogin($auth0_user)
    {
        return call_user_func($this->_on_login_cb, $auth0_user);
    }

    /**
     * Use this to either enable or disable the "remember" function for users.
     *
     * @param null $value
     *
     * @return bool|null
     */
    public function rememberUser($value = null)
    {
        if ($value !== null) {
            $this->remember_user = $value;
        }

        return $this->remember_user;
    }

    /**
     * @param $encUser
     *
     * @return mixed
     */
    public function decodeJWT($encUser)
    {
        try {
            $cache = app()->make('\Auth0\SDK\Helpers\Cache\CacheHandler');
        } catch (BindingResolutionException $e) {
            $cache = null;
        }

        $secret_base64_encoded = env('AUTH0_SECRET_BASE64_ENCODED');

        if (is_null($secret_base64_encoded)) {
          $secret_base64_encoded = true;
        }

        $verifier = new JWTVerifier([
            'valid_audiences'       => [$this->auth0_config['client_id'], $this->auth0_config['api_identifier']],
            'supported_algs'        => (!empty($this->auth0_config['supported_algs'])) ? $this->auth0_config['supported_algs'] : ['HS256'],
            'client_secret'         => $this->auth0_config['client_secret'],
            'authorized_iss'        => $this->auth0_config['authorized_issuers'],
            'secret_base64_encoded' => $secret_base64_encoded,
            'cache'                 => $cache,
        ]);

        $this->apiuser = $verifier->verifyAndDecode($encUser);

        return $this->apiuser;
    }

    public function getIdToken()
    {
        return $this->getSDK()->getIdToken();
    }

    public function getAccessToken()
    {
        return $this->getSDK()->getAccessToken();
    }

    public function jwtuser()
    {
        return $this->apiuser;
    }
}
