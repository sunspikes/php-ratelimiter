<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Throttler;

use Mockery as M;
use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException;
use Sunspikes\Ratelimit\Throttle\Throttler\LeakyBucketThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\MovingWindowThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\ThrottlerInterface;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

class MovingWindowThrottlerTest extends \PHPUnit_Framework_TestCase
{
    const CACHE_TTL = 3600;
    const INITIAL_TIME = 0;
    const HIT_LIMIT = 270;
    const TIME_LIMIT = 240;

    /**
     * @var CacheAdapterInterface|\Mockery\MockInterface
     */
    private $cacheAdapter;

    /**
     * @var TimeAdapterInterface|\Mockery\MockInterface
     */
    private $timeAdapter;

    /**
     * @var ThrottlerInterface
     */
    private $throttler;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->timeAdapter = M::mock(TimeAdapterInterface::class);
        $this->cacheAdapter = M::mock(CacheAdapterInterface::class);

        $this->throttler = new MovingWindowThrottler(
            $this->cacheAdapter,
            $this->timeAdapter,
            'key',
            self::HIT_LIMIT,
            self::TIME_LIMIT,
            self::CACHE_TTL
        );
    }

    public function testAccess()
    {
        //More time has passed than the given window
        $this->mockTimePassed(self::TIME_LIMIT + 2, 3);

        $this->cacheAdapter
            ->shouldReceive('set')
            ->with('key'.MovingWindowThrottler::HITS_CACHE_KEY, 1, self::CACHE_TTL)
            ->once();

        $this->cacheAdapter
            ->shouldReceive('set')
            ->with('key'.LeakyBucketThrottler::TIME_CACHE_KEY, self::TIME_LIMIT + 2, self::CACHE_TTL)
            ->once();

        $this->assertEquals(true, $this->throttler->access());
    }

    public function testClear()
    {
        $this->cacheAdapter
            ->shouldReceive('set')
            ->with('key'.MovingWindowThrottler::HITS_CACHE_KEY, 0, self::CACHE_TTL)
            ->once();

        $this->throttler->clear();
    }

    public function testCountWithMissingCacheItem()
    {
        $this->timeAdapter->shouldReceive('now')->once()->andReturn(self::INITIAL_TIME + 1);
        $this->cacheAdapter->shouldReceive('get')->andThrow(ItemNotFoundException::class);

        self::assertEquals(0, $this->throttler->count());
    }

    public function testCountWithMoreTimePassedThanLimit()
    {
        //More time has passed than the given window
        $this->mockTimePassed(self::TIME_LIMIT + 1, 1);

        $this->assertEquals(0, $this->throttler->count());
    }

    public function testCountWithLessTimePassedThanLimit()
    {
        //Less time has passed than the given window
        $this->mockTimePassed(self::TIME_LIMIT / 6, 1);

        $this->cacheAdapter
            ->shouldReceive('get')
            ->with('key'.MovingWindowThrottler::HITS_CACHE_KEY)
            ->andReturn(self::HIT_LIMIT / 3);

        $this->assertEquals(self::HIT_LIMIT / 6, $this->throttler->count());
    }

    public function testCheck()
    {
        //More time has passed than the given window
        $this->mockTimePassed(self::TIME_LIMIT + 1, 1);

        $this->assertTrue(true, $this->throttler->check());
    }

    /**
     * @param int $timeDiff
     * @param int $numCalls
     */
    private function mockTimePassed($timeDiff, $numCalls)
    {
        $this->timeAdapter->shouldReceive('now')->times($numCalls)->andReturn(self::INITIAL_TIME + $timeDiff);

        $this->cacheAdapter
            ->shouldReceive('get')
            ->with('key'.MovingWindowThrottler::TIME_CACHE_KEY)
            ->andReturn(self::INITIAL_TIME);
    }
}
