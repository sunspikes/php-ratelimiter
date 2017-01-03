<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Throttler;

use Mockery as M;
use Sunspikes\Ratelimit\Throttle\Throttler\FixedWindowThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\ThrottlerInterface;

class FixedWindowThrottlerTest extends AbstractWindowThrottlerTest
{
    public function testAccess()
    {
        $this->cacheAdapter
            ->shouldReceive('set')
            ->with('key'.FixedWindowThrottler::HITS_CACHE_KEY, 1, self::CACHE_TTL)
            ->once();

        $this->cacheAdapter
            ->shouldReceive('set')
            ->with('key'.FixedWindowThrottler::TIME_CACHE_KEY, self::TIME_LIMIT + 2, self::CACHE_TTL)
            ->once();

        parent::testAccess();
    }

    public function testClear()
    {
        $this->timeAdapter->shouldReceive('now')->once()->andReturn(self::INITIAL_TIME + 3);

        $this->cacheAdapter
            ->shouldReceive('set')
            ->with('key'.FixedWindowThrottler::TIME_CACHE_KEY, self::INITIAL_TIME + 3, self::CACHE_TTL)
            ->once();

        $this->cacheAdapter
            ->shouldReceive('set')
            ->with('key'.FixedWindowThrottler::HITS_CACHE_KEY, 0, self::CACHE_TTL)
            ->once();

        $this->throttler->clear();
    }

    public function testCountWithLessTimePassedThanLimit()
    {
        //Less time has passed than the given window
        $this->mockTimePassed(self::TIME_LIMIT / 6);

        $this->cacheAdapter
            ->shouldReceive('get')
            ->with('key'.FixedWindowThrottler::HITS_CACHE_KEY)
            ->andReturn(self::HIT_LIMIT / 3);

        $this->assertEquals(self::HIT_LIMIT / 3, $this->throttler->count());
    }

    public function testGetRetryTimeoutPreLimit()
    {
        //More time has passed than the given window
        $this->mockTimePassed(self::TIME_LIMIT + 1);

        $this->assertEquals(0, $this->throttler->getRetryTimeout());
    }

    public function testGetRetryTimeoutPostLimit()
    {
        //Less time has passed than the given window
        $this->mockTimePassed(self::TIME_LIMIT / 2);
        $this->cacheAdapter
            ->shouldReceive('get')
            ->with('key'.FixedWindowThrottler::HITS_CACHE_KEY)
            ->andReturn(self::HIT_LIMIT + 1);

        $this->assertEquals(
            ThrottlerInterface::SECOND_TO_MILLISECOND_MULTIPLIER / 2 * self::TIME_LIMIT,
            $this->throttler->getRetryTimeout()
        );
    }

    /**
     * @inheritdoc
     */
    protected function createThrottler($key)
    {
        return new FixedWindowThrottler(
            $this->cacheAdapter,
            $this->timeAdapter,
            $key,
            self::HIT_LIMIT,
            self::TIME_LIMIT,
            self::CACHE_TTL
        );
    }

    /**
     * @param int $timeDiff
     */
    protected function mockTimePassed($timeDiff)
    {
        parent::mockTimePassed($timeDiff);

        $this->cacheAdapter
            ->shouldReceive('get')
            ->with('key'.FixedWindowThrottler::TIME_CACHE_KEY)
            ->andReturn(self::INITIAL_TIME);
    }
}
