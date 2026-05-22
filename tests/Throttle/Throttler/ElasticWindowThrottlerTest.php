<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Throttler;

use Mockery as M;
use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Throttle\Throttler\ElasticWindowThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\ThrottlerInterface;

class ElasticWindowThrottlerTest extends TestCase
{
    const TTL = 600;

    private ElasticWindowThrottler $throttler;

    protected function setUp(): void
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

    public function testAccess(): void
    {
        $this->assertTrue($this->throttler->access());
    }

    public function testHit(): void
    {
        $this->throttler->hit();
        $this->assertEquals(1, $this->throttler->count());
    }

    public function testClear(): void
    {
        $this->throttler->clear();
        $this->assertEquals(0, $this->throttler->count());
    }

    public function testCount(): void
    {
        $this->throttler->hit();
        $this->assertEquals(1, $this->throttler->count());
    }

    public function testCheck(): void
    {
        $this->assertTrue($this->throttler->check());
    }

    public function testThrottle(): void
    {
        $this->throttler->hit();
        $this->throttler->hit();
        $this->throttler->hit();
        $this->assertFalse($this->throttler->access());
    }

    public function testGetRetryTimeout(): void
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
