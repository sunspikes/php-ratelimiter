<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Throttler;

use Mockery as M;
use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Throttle\Throttler\RetriableThrottlerInterface;
use Sunspikes\Ratelimit\Throttle\Throttler\RetrialQueueThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\ThrottlerInterface;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

class RetrialQueueThrottlerTest extends \PHPUnit_Framework_TestCase
{
    const HIT_LIMIT = 8;
    const TIME_LIMIT = 24;

    /**
     * @var CacheAdapterInterface|\Mockery\MockInterface
     */
    private $cacheAdapter;

    /**
     * @var ThrottlerInterface|\Mockery\MockInterface
     */
    private $internalThrottler;

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

        $this->internalThrottler = M::mock(RetriableThrottlerInterface::class);
        $this->internalThrottler->shouldReceive('getLimit')->andReturn(self::HIT_LIMIT);
        $this->internalThrottler->shouldReceive('getTime')->andReturn(self::TIME_LIMIT);

        $this->throttler = new RetrialQueueThrottler($this->internalThrottler, $this->timeAdapter);
    }

    public function testAccess()
    {
        $this->internalThrottler->shouldReceive('check')->andReturn(true);
        $this->internalThrottler->shouldReceive('getRetryTimeout')->andReturn(0);
        $this->internalThrottler->shouldReceive('hit')->once();

        $this->timeAdapter->shouldNotReceive('usleep');

        $this->assertTrue($this->throttler->access());
    }

    public function testHitBelowThreshold()
    {
        $this->internalThrottler->shouldReceive('getRetryTimeout')->andReturn(0);
        $this->internalThrottler->shouldReceive('hit')->once()->andReturnSelf();

        $this->timeAdapter->shouldNotReceive('usleep');

        $this->assertEquals($this->internalThrottler, $this->throttler->hit());
    }

    public function testHitOnThreshold()
    {
        $this->internalThrottler->shouldReceive('getRetryTimeout')
            ->andReturn(ThrottlerInterface::SECOND_TO_MILLISECOND_MULTIPLIER);
        $this->internalThrottler->shouldReceive('hit')->once()->andReturnSelf();

        $this->timeAdapter->shouldReceive('usleep')->with(1e6)->once();

        $this->assertEquals($this->internalThrottler, $this->throttler->hit());
    }

    public function testClear()
    {
        $this->internalThrottler->shouldReceive('clear')->once();

        $this->throttler->clear();
    }

    public function testCount()
    {
        $this->internalThrottler->shouldReceive('count')->andReturn(1);

        self::assertEquals(1, $this->throttler->count());
    }

    public function testCheck()
    {
        $this->internalThrottler->shouldReceive('check')->andReturn(true);

        self::assertTrue($this->throttler->check());
    }
}
