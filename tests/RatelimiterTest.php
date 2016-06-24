<?php

namespace Sunspikes\Tests\Ratelimit;

use Mockery as M;
use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException;
use Sunspikes\Ratelimit\RateLimiter;
use Sunspikes\Ratelimit\Throttle\Factory\ThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Hydrator\HydratorFactory;

class RatelimiterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CacheAdapterInterface|M\MockInterface
     */
    private $mockCacheAdapter;

    /**
     * @var RateLimiter
     */
    private $ratelimiter;

    public function setUp()
    {
        $throttlerFactory = new ThrottlerFactory();
        $hydratorFactory =  new HydratorFactory();
        $this->mockCacheAdapter = M::mock(CacheAdapterInterface::class);

        $this->ratelimiter = new RateLimiter($throttlerFactory, $hydratorFactory, $this->mockCacheAdapter, 3, 600);
    }

    public function testThrottlePreLimit()
    {
        $this->mockCacheAdapter->shouldReceive('set')->times(2);
        $this->mockCacheAdapter->shouldReceive('get')->once()->andThrow(ItemNotFoundException::class);
        $this->mockCacheAdapter->shouldReceive('get')->once()->andReturn(2);

        $throttle = $this->ratelimiter->get('pre-limit-test');
        $throttle->hit();
        $throttle->hit();

        $this->assertTrue($throttle->check());
    }

    public function testThrottlePostLimit()
    {
        $this->mockCacheAdapter->shouldReceive('set')->times(3);
        $this->mockCacheAdapter->shouldReceive('get')->once()->andThrow(ItemNotFoundException::class);
        $this->mockCacheAdapter->shouldReceive('get')->twice()->andReturn(2, 3);

        $throttle = $this->ratelimiter->get('post-limit-test');
        $throttle->hit();
        $throttle->hit();
        $throttle->hit();

        $this->assertFalse($throttle->check());
    }

    public function testThrottleAccess()
    {
        $this->mockCacheAdapter->shouldReceive('set')->times(4);
        $this->mockCacheAdapter->shouldReceive('get')->once()->andThrow(ItemNotFoundException::class);
        $this->mockCacheAdapter->shouldReceive('get')->times(3)->andReturn(2, 3, 4);

        $throttle = $this->ratelimiter->get('access-test');
        $throttle->access();
        $throttle->access();
        $throttle->access();

        $this->assertFalse($throttle->access());
    }

    public function testThrottleCount()
    {
        $this->mockCacheAdapter->shouldReceive('set')->times(3);
        $this->mockCacheAdapter->shouldReceive('get')->once()->andThrow(ItemNotFoundException::class);
        $this->mockCacheAdapter->shouldReceive('get')->times(3)->andReturn(2, 3, 3);

        $throttle = $this->ratelimiter->get('count-test');
        $throttle->access();
        $throttle->access();
        $throttle->access();

        $this->assertEquals(3, $throttle->count());
    }
}
