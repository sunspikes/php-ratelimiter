<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Throttler;

use Sunspikes\Ratelimit\Throttle\Throttler\MovingWindowThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\ThrottlerInterface;

class MovingWindowThrottlerTest extends AbstractWindowThrottlerTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheAdapter->shouldReceive('get')->with('key')->andReturn(serialize([]))->byDefault();
    }

    public function testAccess(): void
    {
        $this->cacheAdapter->shouldReceive('get')
            ->with('key')
            ->andReturn(serialize([self::INITIAL_TIME - self::TIME_LIMIT - 1 => self::HIT_LIMIT + 1]));

        $this->cacheAdapter->shouldReceive('set')
            ->with('key', serialize([self::INITIAL_TIME + self::TIME_LIMIT + 2 => 1]), self::CACHE_TTL)
            ->once();

        parent::testAccess();
    }

    public function testClear(): void
    {
        $this->expectNotToPerformAssertions();

        $this->cacheAdapter->shouldReceive('set')
            ->with('key', serialize([]), self::CACHE_TTL)
            ->once();

        $this->throttler->clear();
    }

    public function testCountWithLessTimePassedThanLimit(): void
    {
        $this->mockTimePassed(self::TIME_LIMIT / 6);

        $this->cacheAdapter->shouldReceive('get')
            ->with('key')
            ->andReturn(serialize([
                self::INITIAL_TIME - self::TIME_LIMIT => self::HIT_LIMIT / 2,
                self::INITIAL_TIME => self::HIT_LIMIT / 3,
            ]));

        $this->assertEquals(self::HIT_LIMIT / 3, $this->throttler->count());
    }

    public function testGetRetryTimeoutPreLimit(): void
    {
        $this->mockTimePassed(self::TIME_LIMIT + 1);

        $this->cacheAdapter->shouldReceive('get')
            ->with('key')
            ->andReturn(serialize([self::INITIAL_TIME - self::TIME_LIMIT - 1 => self::HIT_LIMIT + 1]));

        $this->assertEquals(0, $this->throttler->getRetryTimeout());
    }

    public function testGetRetryTimeoutPostLimit(): void
    {
        $this->mockTimePassed(1);

        $this->cacheAdapter->shouldReceive('get')
            ->with('key')
            ->andReturn(serialize([
                self::INITIAL_TIME => 1,
                self::INITIAL_TIME + 1 => 1,
                self::INITIAL_TIME + self::TIME_LIMIT - 1 => self::HIT_LIMIT - 2
            ]));

        $this->assertEquals(
            ThrottlerInterface::SECOND_TO_MILLISECOND_MULTIPLIER * (self::TIME_LIMIT - 1),
            $this->throttler->getRetryTimeout()
        );
    }

    protected function createThrottler(string $key): MovingWindowThrottler
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
