<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Throttler;

use Mockery as M;
use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Throttle\Throttler\ElasticWindowThrottler;

class ElasticWindowThrottlerTest extends \PHPUnit_Framework_TestCase
{
    private $throttler;

    public function setUp()
    {
        $cacheAdapter = M::mock(CacheAdapterInterface::class);

        $cacheAdapter->shouldReceive('set')
            ->withAnyArgs()
            ->andReturnNull();

        $cacheAdapter->shouldReceive('get')
            ->with('key')
            ->andReturn(0, 1, 2, 3, 4);

        $this->throttler = new ElasticWindowThrottler($cacheAdapter, 'key', 3, 600);
    }

    public function testAccess()
    {
        $this->assertEquals(true, $this->throttler->access());
    }

    public function testHit()
    {
        $this->assertEquals(1, count($this->throttler->hit()));
    }

    public function testClear()
    {
        $this->assertEquals(0, count($this->throttler->clear()));
    }

    public function testCount()
    {
        $this->throttler->hit();
        $this->assertEquals(1, $this->throttler->count());
    }

    public function testCheck()
    {
        $this->assertTrue($this->throttler->check());
    }

    public function testThrottle()
    {
        $this->throttler->hit();
        $this->throttler->hit();
        $this->throttler->hit();
        $this->assertFalse($this->throttler->access());
    }
}
