<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Throttler;

use Mockery as M;
use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Throttle\Throttler\RetriableThrottlerInterface;
use Sunspikes\Ratelimit\Throttle\Throttler\RetrialQueueThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\ThrottlerInterface;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

class RetrialQueueThrottlerTest extends TestCase
{
    const HIT_LIMIT = 8;
    const TIME_LIMIT = 24;

    private CacheAdapterInterface|M\MockInterface $cacheAdapter;
    private RetriableThrottlerInterface|M\MockInterface $internalThrottler;
    private TimeAdapterInterface|M\MockInterface $timeAdapter;
    private RetrialQueueThrottler $throttler;

    protected function setUp(): void
    {
        $this->timeAdapter = M::mock(TimeAdapterInterface::class);
        $this->cacheAdapter = M::mock(CacheAdapterInterface::class);

        $this->internalThrottler = M::mock(RetriableThrottlerInterface::class);
        $this->internalThrottler->shouldReceive('getLimit')->andReturn(self::HIT_LIMIT);
        $this->internalThrottler->shouldReceive('getTime')->andReturn(self::TIME_LIMIT);

        $this->throttler = new RetrialQueueThrottler($this->internalThrottler, $this->timeAdapter);
    }

    public function testAccess(): void
    {
        $this->internalThrottler->shouldReceive('check')->andReturn(true);
        $this->internalThrottler->shouldReceive('getRetryTimeout')->andReturn(0);
        $this->internalThrottler->shouldReceive('hit')->once();

        $this->timeAdapter->shouldNotReceive('usleep');

        $this->assertTrue($this->throttler->access());
    }

    public function testHitBelowThreshold(): void
    {
        $this->internalThrottler->shouldReceive('getRetryTimeout')->andReturn(0);
        $this->internalThrottler->shouldReceive('hit')->once()->andReturnSelf();

        $this->timeAdapter->shouldNotReceive('usleep');

        $this->assertEquals($this->internalThrottler, $this->throttler->hit());
    }

    public function testHitOnThreshold(): void
    {
        $this->internalThrottler->shouldReceive('getRetryTimeout')
            ->andReturn(ThrottlerInterface::SECOND_TO_MILLISECOND_MULTIPLIER);
        $this->internalThrottler->shouldReceive('hit')->once()->andReturnSelf();

        $this->timeAdapter->shouldReceive('usleep')->with(1e6)->once();

        $this->assertEquals($this->internalThrottler, $this->throttler->hit());
    }

    public function testClear(): void
    {
        $this->expectNotToPerformAssertions();

        $this->internalThrottler->shouldReceive('clear')->once();

        $this->throttler->clear();
    }

    public function testCount(): void
    {
        $this->internalThrottler->shouldReceive('count')->andReturn(1);

        self::assertEquals(1, $this->throttler->count());
    }

    public function testCheck(): void
    {
        $this->internalThrottler->shouldReceive('check')->andReturn(true);

        self::assertTrue($this->throttler->check());
    }
}
