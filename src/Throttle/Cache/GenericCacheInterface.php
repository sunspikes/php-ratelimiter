<?php

namespace Sunspikes\src\Throttle\Cache;

interface GenericCacheInterface
{
    /**
     * @param string $key
     * @param mixed  $item
     *
     * @return mixed
     */
    public function setItem(string $key, $item);

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getItem(string $key);

    /**
     * @param string $key
     */
    public function remove(string $key);

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasItem(string $key): bool;

    /**
     * @param string $key
     *
     * @return bool
     */
    public function isExpired(string $key): bool;
}
