<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Throttler;

use Mockery as M;
use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException;
use Sunspikes\Ratelimit\Throttle\Throttler\AbstractWindowThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\RetriableThrottlerInterface;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

abstract class AbstractWindowThrottlerTest extends TestCase
{
    const CACHE_TTL = 3600;
    const INITIAL_TIME = 0;
    const HIT_LIMIT = 270;
    const TIME_LIMIT = 240;

    protected CacheAdapterInterface|M\MockInterface $cacheAdapter;
    protected TimeAdapterInterface|M\MockInterface $timeAdapter;
    protected RetriableThrottlerInterface $throttler;

    protected function setUp(): void
    {
        $this->timeAdapter = M::mock(TimeAdapterInterface::class);
        $this->cacheAdapter = M::mock(CacheAdapterInterface::class);

        $this->throttler = $this->createThrottler('key');
    }

    public function testAccess(): void
    {
        $this->mockTimePassed(self::TIME_LIMIT + 2);

        $this->assertEquals(true, $this->throttler->access());
    }

    public function testCountWithMissingCacheItem(): void
    {
        $this->timeAdapter->shouldReceive('now')->once()->andReturn(self::INITIAL_TIME + 1);
        $this->cacheAdapter->shouldReceive('get')->andThrow(ItemNotFoundException::class);

        self::assertEquals(0, $this->throttler->count());
    }

    public function testCountWithMoreTimePassedThanLimit(): void
    {
        $this->mockTimePassed(self::TIME_LIMIT + 1);

        $this->assertEquals(0, $this->throttler->count());
    }

    public function testCheck(): void
    {
        $this->mockTimePassed(self::TIME_LIMIT + 1);

        $this->assertTrue($this->throttler->check());
    }

    abstract public function testCountWithLessTimePassedThanLimit(): void;

    abstract protected function createThrottler(string $key): AbstractWindowThrottler;

    protected function mockTimePassed(int $timeDiff): void
    {
        $this->timeAdapter->shouldReceive('now')->andReturn(self::INITIAL_TIME + $timeDiff);
    }
}
