<?php

namespace Sunspikes\Tests\Ratelimit\Functional;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Adapter\Redis\RedisCachePool;
use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Cache\ThrottlerCache;
use Sunspikes\Ratelimit\Cache\ThrottlerCacheInterface;
use Sunspikes\Ratelimit\RateLimiter;

abstract class AbstractThrottlerTestCase extends TestCase
{
    /**
     * @var Ratelimiter
     */
    protected $ratelimiter;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $pool = $this->getCachePool();
        $cache = new ThrottlerCache($pool);

        $this->ratelimiter = $this->createRatelimiter($cache);
    }

    /**
     * Get the cache pool adapter to use.
     *
     * @return ArrayCachePool|RedisCachePool
     */
    private function getCachePool()
    {
        if (class_exists(\Redis::class)) {
            $redis = new \Redis();
            try {
                if (true === $redis->connect('localhost')) {
                    return new RedisCachePool($redis);
                }
            } catch (\Exception $e) {
            }
        }

        return new ArrayCachePool();
    }

    public function testThrottlePreLimit()
    {
        $key = $this->getRateLimiterKey('pre-limit-test');
        $throttle = $this->ratelimiter->get($key);

        for ($i = 0; ++$i < $this->getMaxAttempts();) {
            $throttle->hit();
        }

        $this->assertTrue($throttle->check());
    }

    public function testThrottlePostLimit()
    {
        $key = $this->getRateLimiterKey('post-limit-test');
        $throttle = $this->ratelimiter->get($key);

        for ($i = 0; $i < $this->getMaxAttempts(); ++$i) {
            $throttle->hit();
        }

        $this->assertFalse($throttle->check());
    }

    public function testThrottleAccess()
    {
        $key = $this->getRateLimiterKey('access-test');
        $throttle = $this->ratelimiter->get($key);

        for ($i = 0; $i < $this->getMaxAttempts(); ++$i) {
            $throttle->access();
        }

        $this->assertFalse($throttle->access());
    }

    public function testThrottleCount()
    {
        $key = $this->getRateLimiterKey('count-test');
        $throttle = $this->ratelimiter->get($key);

        for ($i = 0; $i < $this->getMaxAttempts(); ++$i) {
            $throttle->access();
        }

        $this->assertEquals(3, $throttle->count());
    }

    public function testClear()
    {
        $key = $this->getRateLimiterKey('clear-test');
        $throttle = $this->ratelimiter->get($key);
        $throttle->hit();
        $throttle->clear();

        self::assertEquals(0, $throttle->count());
    }

    /**
     * Get an unique key based on throttling mode.
     *
     * @param string $key
     *
     * @return string
     */
    protected function getRateLimiterKey(string $key): string
    {
        return $key.'-'.sha1(static::class.mt_rand());
    }

    /**
     * @return int
     */
    protected function getMaxAttempts()
    {
        return 3;
    }

    /**
     * @param ThrottlerCacheInterface $throttlerCache
     *
     * @return RateLimiter
     */
    abstract protected function createRatelimiter(ThrottlerCacheInterface $throttlerCache);
}
