<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Throttler;

use Mockery as M;
use Sunspikes\Ratelimit\Throttle\Throttler\FixedWindowThrottler;

class FixedWindowThrottlerTest extends AbstractWindowThrottlerTest
{
    public function testAccess()
    {
        //More time has passed than the given window
        $this->mockTimePassed(self::TIME_LIMIT + 2, 4);

        parent::testAccess();
    }

    public function testClear()
    {
        $this->timeAdapter->shouldReceive('now')->once()->andReturn(self::INITIAL_TIME + 3);

        $this->cacheAdapter
            ->shouldReceive('set')
            ->with('key'.FixedWindowThrottler::TIME_CACHE_KEY, self::INITIAL_TIME + 3, self::CACHE_TTL)
            ->once();

        parent::testClear();
    }

    public function testCountWithLessTimePassedThanLimit()
    {
        //Less time has passed than the given window
        $this->mockTimePassed(self::TIME_LIMIT / 6, 1);

        $this->cacheAdapter
            ->shouldReceive('get')
            ->with('key'.FixedWindowThrottler::HITS_CACHE_KEY)
            ->andReturn(self::HIT_LIMIT / 3);

        $this->assertEquals(self::HIT_LIMIT / 3, $this->throttler->count());
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
}
