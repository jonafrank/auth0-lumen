<?php
namespace Auth0\Lumen;

use Auth0\SDK\Store\StoreInterface;

/**
 * As lumen 5.2+ disabled sessions we implement default PHP Sessions here.
 */
class LumenSessionStore implements StoreInterface
{
    const BASE_NAME = 'auth0_';
    /**
     * Checks if session is started.
     */
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Constructs a session var name.
     *
     * @param string $key
     *
     * @return string
     */
    public function getSessionKeyName($key)
    {
        return self::BASE_NAME.'_'.$key;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $key_name = $this->getSessionKeyName($key);
        $_SESSION[$key_name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        $key_name = $this->getSessionKeyName($key);
        if (isset($_SESSION[$key_name])) {
            return $_SESSION[$key_name];
        }
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        $key_name = $this->getSessionKeyName($key);
        unset($_SESSION[$key_name]);
    }
}
