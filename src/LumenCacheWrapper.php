<?php

namespace Auth0\Lumen;

use Illuminate\Cache\Repository;
use Auth0\SDK\Helpers\Cache\CacheHandler;

class LumenCacheWrapper implements CacheHandler
{
    protected $cache;

    /**
     * LumenCacheWrapper constructor.
     *
     * @param Repository $laravelCache
     */
    public function __construct(Repository $laravelCache)
    {
        $this->cache = $laravelCache;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->cache->get($key);
    }

    /**
     * @param $key
     */
    public function delete($key)
    {
        $this->cache->forget($key);
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $this->cache->forever($key, $value);
    }
}
