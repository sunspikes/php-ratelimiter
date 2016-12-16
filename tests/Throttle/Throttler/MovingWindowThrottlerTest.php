<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Throttler;

use Mockery as M;
use Sunspikes\Ratelimit\Throttle\Throttler\MovingWindowThrottler;

class MovingWindowThrottlerTest extends AbstractWindowThrottlerTest
{
    public function testAccess()
    {
        //More time has passed than the given window
        $this->mockTimePassed(self::TIME_LIMIT + 2, 3);

        parent::testAccess();
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

    public function testGetRetryTimeoutPreLimit()
    {
        $this->mockTimePassed(self::TIME_LIMIT + 1, 1);

        $this->assertEquals(0, $this->throttler->getRetryTimeout());
    }

    public function testGetRetryTimeoutPostLimit()
    {
        $this->mockTimePassed(0, 1);
        $this->cacheAdapter
            ->shouldReceive('get')
            ->with('key'.MovingWindowThrottler::HITS_CACHE_KEY)
            ->andReturn(self::HIT_LIMIT * 1.5 - 1);

        $this->assertEquals(5e2 * self::TIME_LIMIT, $this->throttler->getRetryTimeout());
    }

    /**
     * @inheritdoc
     */
    protected function createThrottler($key)
    {
        return new MovingWindowThrottler(
            $this->cacheAdapter,
            $this->timeAdapter,
            $key,
            self::HIT_LIMIT,
            self::TIME_LIMIT,
            self::CACHE_TTL
        );
    }
}
