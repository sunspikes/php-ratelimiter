<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Throttler;

use Mockery as M;
use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException;
use Sunspikes\Ratelimit\Throttle\Throttler\LeakyBucketThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\ThrottlerInterface;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

class LeakyBucketThrottlerTest extends TestCase
{
    const CACHE_TTL = 3600;
    const INITIAL_TIME = 0;
    const TOKEN_LIMIT = 270;
    const TIME_LIMIT = 24000;
    const THRESHOLD = 30;

    private CacheAdapterInterface|M\MockInterface $cacheAdapter;
    private TimeAdapterInterface|M\MockInterface $timeAdapter;
    private LeakyBucketThrottler $throttler;

    protected function setUp(): void
    {
        $this->timeAdapter = M::mock(TimeAdapterInterface::class);
        $this->cacheAdapter = M::mock(CacheAdapterInterface::class);

        $this->throttler = new LeakyBucketThrottler(
            $this->cacheAdapter,
            $this->timeAdapter,
            'key',
            self::TOKEN_LIMIT,
            self::TIME_LIMIT,
            self::THRESHOLD,
            self::CACHE_TTL
        );
    }

    public function testAccess(): void
    {
        $this->mockTimePassed(self::TIME_LIMIT + 1, 3);
        $this->mockSetUsedCapacity(
            1,
            (self::INITIAL_TIME + self::TIME_LIMIT + 1) / ThrottlerInterface::SECOND_TO_MILLISECOND_MULTIPLIER
        );

        $this->assertTrue($this->throttler->access());
    }

    public function testHitBelowThreshold(): void
    {
        $this->mockTimePassed(0, 2);

        $this->cacheAdapter
            ->shouldReceive('get')
            ->with('key' . LeakyBucketThrottler::CACHE_KEY_TOKEN)
            ->andReturn(self::THRESHOLD - 1);

        $this->mockSetUsedCapacity(self::THRESHOLD, self::INITIAL_TIME);

        $this->assertEquals(0, $this->throttler->hit());
    }

    public function testHitOnThreshold(): void
    {
        $this->mockTimePassed(0, 2);

        $this->cacheAdapter
            ->shouldReceive('get')
            ->with('key' . LeakyBucketThrottler::CACHE_KEY_TOKEN)
            ->andReturn(self::THRESHOLD);

        $this->mockSetUsedCapacity(self::THRESHOLD + 1, self::INITIAL_TIME);

        $expectedWaitTime = self::TIME_LIMIT / (self::TOKEN_LIMIT - self::THRESHOLD);
        $this->timeAdapter->shouldReceive('usleep')
            ->with(ThrottlerInterface::MILLISECOND_TO_MICROSECOND_MULTIPLIER * $expectedWaitTime)
            ->once()
            ->ordered();

        $this->assertEquals($expectedWaitTime, $this->throttler->hit());
    }

    public function testClear(): void
    {
        $this->expectNotToPerformAssertions();

        $this->timeAdapter->shouldReceive('now')->once()->andReturn(self::INITIAL_TIME + 1);
        $this->mockSetUsedCapacity(0, self::INITIAL_TIME + 1);

        $this->throttler->clear();
    }

    public function testCountWithMissingCacheItem(): void
    {
        $this->timeAdapter->shouldReceive('now')->once()->andReturn(self::INITIAL_TIME + 1);
        $this->cacheAdapter->shouldReceive('get')->andThrow(ItemNotFoundException::class);

        $this->mockSetUsedCapacity(0, self::INITIAL_TIME + 1);

        self::assertEquals(0, $this->throttler->count());
    }

    public function testCountWithMoreTimePassedThanLimit(): void
    {
        $this->mockTimePassed(self::TIME_LIMIT + 1, 1);

        $this->assertEquals(0, $this->throttler->count());
    }

    public function testCountWithLessTimePassedThanLimit(): void
    {
        $this->mockTimePassed(self::TIME_LIMIT / 6, 1);

        $this->cacheAdapter
            ->shouldReceive('get')
            ->with('key' . LeakyBucketThrottler::CACHE_KEY_TOKEN)
            ->andReturn(self::TOKEN_LIMIT / 2);

        $this->assertEquals(self::TOKEN_LIMIT / 3, $this->throttler->count());
    }

    public function testCheck(): void
    {
        $this->mockTimePassed(self::TIME_LIMIT + 1, 1);

        $this->assertTrue($this->throttler->check());
    }

    public function testCheckBelowThreshold(): void
    {
        $this->mockTimePassed(0, 1);

        $this->cacheAdapter
            ->shouldReceive('get')
            ->with('key' . LeakyBucketThrottler::CACHE_KEY_TOKEN)
            ->andReturn(self::THRESHOLD - 1);

        $this->assertTrue($this->throttler->check());
    }

    public function testCheckOnThreshold(): void
    {
        $this->mockTimePassed(0, 1);

        $this->cacheAdapter
            ->shouldReceive('get')
            ->with('key' . LeakyBucketThrottler::CACHE_KEY_TOKEN)
            ->andReturn(self::THRESHOLD);

        $this->assertFalse($this->throttler->check());
    }

    public function testGetRetryTimeoutPreLimit(): void
    {
        $this->mockTimePassed(self::TIME_LIMIT + 2, 1);

        $this->assertEquals(0, $this->throttler->getRetryTimeout());
    }

    public function testGetRetryTimeoutPostLimit(): void
    {
        $this->mockTimePassed(0, 1);

        $this->cacheAdapter
            ->shouldReceive('get')
            ->with('key' . LeakyBucketThrottler::CACHE_KEY_TOKEN)
            ->andReturn(self::THRESHOLD);

        $this->assertSame((int) ceil(self::TIME_LIMIT / self::TOKEN_LIMIT), $this->throttler->getRetryTimeout());
    }

    public function testGetRetryTimeoutBelowThreshold(): void
    {
        $this->mockTimePassed(0, 1);

        $this->cacheAdapter
            ->shouldReceive('get')
            ->with('key' . LeakyBucketThrottler::CACHE_KEY_TOKEN)
            ->andReturn(self::THRESHOLD - 1);

        $this->assertEquals(0, $this->throttler->getRetryTimeout());
    }

    private function mockSetUsedCapacity(int $tokens, float $time): void
    {
        $this->cacheAdapter
            ->shouldReceive('set')
            ->with('key' . LeakyBucketThrottler::CACHE_KEY_TOKEN, $tokens, self::CACHE_TTL)
            ->once()
            ->ordered('set-cache');

        $this->cacheAdapter
            ->shouldReceive('set')
            ->with('key' . LeakyBucketThrottler::CACHE_KEY_TIME, $time, self::CACHE_TTL)
            ->once()
            ->ordered('set-cache');
    }

    private function mockTimePassed(int $timeDiff, int $numCalls): void
    {
        $this->timeAdapter->shouldReceive('now')
            ->times($numCalls)
            ->andReturn((self::INITIAL_TIME + $timeDiff) / ThrottlerInterface::SECOND_TO_MILLISECOND_MULTIPLIER);

        $this->cacheAdapter
            ->shouldReceive('get')
            ->with('key' . LeakyBucketThrottler::CACHE_KEY_TIME)
            ->andReturn(self::INITIAL_TIME / ThrottlerInterface::SECOND_TO_MILLISECOND_MULTIPLIER);
    }
}
