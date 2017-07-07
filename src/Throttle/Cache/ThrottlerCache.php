<?php

namespace Sunspikes\src\Throttle\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Sunspikes\src\Throttle\Cache\Bridge\CacheItem;

class ThrottlerCache implements ThrottlerCacheInterface
{
    const CACHE_ITEM_EXPIRY = 0;
    
    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;

    /**
     * ThrottlerCache constructor.
     *
     * @param CacheItemPoolInterface $cacheItemPool
     */
    public function __construct(CacheItemPoolInterface $cacheItemPool)
    {
        $this->cacheItemPool = $cacheItemPool;
    }

    /**
     * @param string $key
     * @param string $count
     * @param string $limit
     * @param string $ttl
     */
    public function set(string $key, string $count, string $limit, string $ttl = null)
    {
        $params = json_encode([
            'count' => $count,
            'limit' => $limit,
            'ttl' => $ttl ?? microtime(true),
        ]);

        $this->setParams($key, $params);
    }

    /**
     * @param string $key
     *
     * @return int|null
     */
    public function count(string $key)
    {
        $params = $this->getParams($key);
        $count = $params ? (int) $params['count'] : null;

        return $count;
    }

    /**
     * @param string $key
     */
    public function increment(string $key)
    {
        $params = $this->getParams($key);
        $params['count']++;

        $this->setParams($key, $params);
    }

    /**
     * @param string $key
     */
    public function remove(string $key)
    {
        $this->cacheItemPool->deleteItem($key);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasItem(string $key): bool
    {
        return $this->cacheItemPool->hasItem($key);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function isExpired(string $key): bool
    {
        $params = $this->getParams($key);

        return microtime(true) < $params['ttl'];
    }

    /**
     * @param string $key
     *
     * @return array|null
     */
    private function getParams(string $key)
    {
        $item = $this->cacheItemPool->getItem($key);
        $params = $item ? json_decode($item->get()) : null;

        return $params;
    }

    /**
     * @param string $key
     * @param array  $params
     *
     * @return CacheItemInterface
     */
    private function setParams(string $key, array $params): CacheItemInterface
    {
        $item = $this->cacheItemPool->getItem($key) ?? new CacheItem($key);
        $item->set(json_encode($params));
        $item->expiresAt(self::CACHE_ITEM_EXPIRY);

        $this->cacheItemPool->deleteItem($key);
        $this->cacheItemPool->save($item);

        return $item;
    }
}