<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Throttler;

use Mockery as M;
use Sunspikes\Ratelimit\Throttle\Throttler\MovingWindowThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\ThrottlerInterface;

class MovingWindowThrottlerTest extends AbstractWindowThrottlerTest
{
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->cacheAdapter->shouldReceive('get')->with('key')->andReturn(serialize([]))->byDefault();
    }

    public function testAccess()
    {
        $this->cacheAdapter->shouldReceive('get')
            ->with('key')
            ->andReturn(serialize([self::INITIAL_TIME - self::TIME_LIMIT - 1 => self::HIT_LIMIT + 1]));

        $this->cacheAdapter->shouldReceive('set')
            ->with('key', serialize([self::INITIAL_TIME + self::TIME_LIMIT + 2 => 1]), self::CACHE_TTL)
            ->once();

        parent::testAccess();
    }

    public function testClear()
    {
        $this->cacheAdapter->shouldReceive('set')
            ->with('key', serialize([]), self::CACHE_TTL)
            ->once();

        $this->throttler->clear();
    }

    public function testCountWithLessTimePassedThanLimit()
    {
        //Less time has passed than the given window
        $this->mockTimePassed(self::TIME_LIMIT / 6);

        $this->cacheAdapter->shouldReceive('get')
            ->with('key')
            ->andReturn(serialize([
                self::INITIAL_TIME - self::TIME_LIMIT => self::HIT_LIMIT / 2,
                self::INITIAL_TIME => self::HIT_LIMIT / 3,
            ]));

        $this->assertEquals(self::HIT_LIMIT / 3, $this->throttler->count());
    }

    public function testGetRetryTimeoutPreLimit()
    {
        $this->mockTimePassed(self::TIME_LIMIT + 1);

        $this->cacheAdapter->shouldReceive('get')
            ->with('key')
            ->andReturn(serialize([self::INITIAL_TIME - self::TIME_LIMIT - 1 => self::HIT_LIMIT + 1]));

        $this->assertEquals(0, $this->throttler->getRetryTimeout());
    }

    public function testGetRetryTimeoutPostLimit()
    {
        $this->mockTimePassed(1);

        $this->cacheAdapter->shouldReceive('get')
            ->with('key')
            ->andReturn(serialize([
                self::INITIAL_TIME => 1,
                self::INITIAL_TIME + 1 => 1,    // <-- This is the timestamp which should expire before can be retried
                self::INITIAL_TIME + self::TIME_LIMIT - 1 => self::HIT_LIMIT - 2
            ]));

        $this->assertEquals(
            ThrottlerInterface::SECOND_TO_MILLISECOND_MULTIPLIER * (self::TIME_LIMIT - 1),
            $this->throttler->getRetryTimeout()
        );
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
