<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Throttler;

use Mockery as M;
use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Throttle\Throttler\ElasticWindowThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\ThrottlerInterface;

class ElasticWindowThrottlerTest extends \PHPUnit_Framework_TestCase
{
    const TTL = 600;

    /**
     * @var ElasticWindowThrottler
     */
    private $throttler;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $cacheAdapter = M::mock(CacheAdapterInterface::class);

        $cacheAdapter->shouldReceive('set')
            ->withAnyArgs()
            ->andReturnNull();

        $cacheAdapter->shouldReceive('get')
            ->with('key')
            ->andReturn(0, 1, 2, 3, 4);

        $this->throttler = new ElasticWindowThrottler($cacheAdapter, 'key', 3, self::TTL);
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

    public function testGetRetryTimeout()
    {
        $this->assertEquals(0, $this->throttler->getRetryTimeout());

        $this->throttler->hit();
        $this->throttler->hit();
        $this->throttler->hit();

        $this->assertEquals(
            ThrottlerInterface::SECOND_TO_MILLISECOND_MULTIPLIER * self::TTL,
            $this->throttler->getRetryTimeout()
        );
    }
}
