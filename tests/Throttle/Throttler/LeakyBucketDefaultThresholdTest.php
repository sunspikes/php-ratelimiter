<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Throttler;

use Mockery as M;
use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException;
use Sunspikes\Ratelimit\Throttle\Throttler\LeakyBucketThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\ThrottlerInterface;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

class LeakyBucketDefaultThresholdTest extends TestCase
{
    const TOKEN_LIMIT = 30;
    const TIME_LIMIT = 27000;
    const CACHE_TTL = 3600;

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
            null,
            self::CACHE_TTL
        );
    }

    public function testCheckWithEmptyBucketAndDefaultThreshold(): void
    {
        $this->timeAdapter->shouldReceive('now')->andReturn(100.0);
        $this->cacheAdapter->shouldReceive('get')
            ->with('key' . LeakyBucketThrottler::CACHE_KEY_TIME)
            ->andThrow(ItemNotFoundException::class);

        $this->cacheAdapter->shouldReceive('set')->withAnyArgs();

        $this->assertFalse($this->throttler->check());
    }

    public function testHitWithDefaultThresholdAlwaysSleeps(): void
    {
        $this->timeAdapter->shouldReceive('now')->andReturn(100.0);
        $this->cacheAdapter->shouldReceive('get')
            ->with('key' . LeakyBucketThrottler::CACHE_KEY_TIME)
            ->andThrow(ItemNotFoundException::class);

        $this->cacheAdapter->shouldReceive('set')->withAnyArgs();

        $expectedWaitTime = (int) ceil(self::TIME_LIMIT / self::TOKEN_LIMIT);
        $this->timeAdapter->shouldReceive('usleep')
            ->with(ThrottlerInterface::MILLISECOND_TO_MICROSECOND_MULTIPLIER * $expectedWaitTime)
            ->once();

        $this->assertEquals($expectedWaitTime, $this->throttler->hit());
    }

    public function testGetRetryTimeoutWithDefaultThresholdNeverZero(): void
    {
        $this->timeAdapter->shouldReceive('now')->andReturn(100.0);
        $this->cacheAdapter->shouldReceive('get')
            ->with('key' . LeakyBucketThrottler::CACHE_KEY_TIME)
            ->andThrow(ItemNotFoundException::class);

        $this->cacheAdapter->shouldReceive('set')->withAnyArgs();

        $this->assertGreaterThan(0, $this->throttler->getRetryTimeout());
    }

    public function testGetRetryTimeoutConsistentWithCheck(): void
    {
        $this->timeAdapter->shouldReceive('now')->andReturn(100.0);
        $this->cacheAdapter->shouldReceive('get')
            ->with('key' . LeakyBucketThrottler::CACHE_KEY_TIME)
            ->andReturn(50.0);
        $this->cacheAdapter->shouldReceive('get')
            ->with('key' . LeakyBucketThrottler::CACHE_KEY_TOKEN)
            ->andReturn(0);

        $this->cacheAdapter->shouldReceive('set')->withAnyArgs();

        $retryTimeout = $this->throttler->getRetryTimeout();
        $check = $this->throttler->check();

        self::assertFalse($check);
        self::assertGreaterThan(0, $retryTimeout);
    }
}
