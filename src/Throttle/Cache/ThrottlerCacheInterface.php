<?php

namespace Sunspikes\src\Throttle\Cache;

interface ThrottlerCacheInterface
{
    /**
     * @param string $key
     * @param string $count
     * @param string $limit
     * @param string $ttl
     */
    public function set(string $key, string $count, string $limit, string $ttl = null);

    /**
     * @param string $key
     *
     * @return int|null
     */
    public function count(string $key);

    /**
     * @param string $key
     */
    public function increment(string $key);
}