<?php

namespace Sunspikes\Tests\Ratelimit\Cache;

use Cache\Adapter\Common\CacheItem;
use Cache\Adapter\Common\Exception\CachePoolException;
use Mockery as M;
use Psr\Cache\CacheItemPoolInterface;
use Sunspikes\Ratelimit\Cache\ThrottlerCache;
use Sunspikes\Ratelimit\Throttle\Entity\CacheCount;

class ThrottlerCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testGetItemFound()
    {
        $key = 'key';
        $countItem = new CacheCount(1);

        $cacheItem = new CacheItem($key);
        $cacheItem->set(json_encode(['class' => CacheCount::class, 'data' => $countItem]));

        $cacheItemPool = M::mock(CacheItemPoolInterface::class);
        $cacheItemPool->shouldReceive('getItem')
            ->with($key)
            ->andReturn($cacheItem);

        $cache = new ThrottlerCache($cacheItemPool);
        $gotItem = $cache->getItem($key);

        $this->assertEquals($countItem, $gotItem);
    }

    /**
     * @expectedException \Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException
     */
    public function testGetItemNotFound()
    {
        $key = 'key';
        $cacheItemPool = M::mock(CacheItemPoolInterface::class);
        $cacheItemPool->shouldReceive('getItem')
            ->with($key)
            ->andThrow(CachePoolException::class);

        $cache = new ThrottlerCache($cacheItemPool);
        $cache->getItem($key);
    }

    public function testHasItem()
    {
        $key = 'key';
        $cacheItemPool = M::mock(CacheItemPoolInterface::class);
        $cacheItemPool->shouldReceive('hasItem')
            ->with($key)
            ->andReturn(true);

        $cache = new ThrottlerCache($cacheItemPool);
        $hasItem = $cache->hasItem($key);

        $this->assertEquals(true, $hasItem);
    }

    public function testSetItem()
    {
        $key = 'key';
        $countItem = new CacheCount(1);

        $cacheItemPool = M::mock(CacheItemPoolInterface::class);
        $cacheItemPool->shouldReceive('getItem')
            ->with($key)
            ->andReturn(new CacheItem($key));
        $cacheItemPool->shouldReceive('save')
            ->with(M::type(CacheItem::class))
            ->andReturn(true);

        $cache = new ThrottlerCache($cacheItemPool);
        $status = $cache->setItem($key, $countItem);

        $this->assertEquals(true, $status);
    }
}