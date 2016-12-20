<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Throttler;

use Mockery as M;
use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException;
use Sunspikes\Ratelimit\Throttle\Throttler\AbstractWindowThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\RetriableThrottlerInterface;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

abstract class AbstractWindowThrottlerTest extends \PHPUnit_Framework_TestCase
{
    const CACHE_TTL = 3600;
    const INITIAL_TIME = 0;
    const HIT_LIMIT = 270;
    const TIME_LIMIT = 240;

    /**
     * @var CacheAdapterInterface|\Mockery\MockInterface
     */
    protected $cacheAdapter;

    /**
     * @var TimeAdapterInterface|\Mockery\MockInterface
     */
    protected $timeAdapter;

    /**
     * @var RetriableThrottlerInterface
     */
    protected $throttler;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->timeAdapter = M::mock(TimeAdapterInterface::class);
        $this->cacheAdapter = M::mock(CacheAdapterInterface::class);

        $this->throttler = $this->createThrottler('key');
    }

    public function testAccess()
    {
        $this->mockTimePassed(self::TIME_LIMIT + 2);

        $this->assertEquals(true, $this->throttler->access());
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
        $this->mockTimePassed(self::TIME_LIMIT + 1);

        $this->assertEquals(0, $this->throttler->count());
    }

    public function testCheck()
    {
        //More time has passed than the given window
        $this->mockTimePassed(self::TIME_LIMIT + 1);

        $this->assertTrue($this->throttler->check());
    }

    abstract public function testCountWithLessTimePassedThanLimit();

    /**
     * @param string $key
     *
     * @return AbstractWindowThrottler
     */
    abstract protected function createThrottler($key);

    /**
     * @param int $timeDiff
     */
    protected function mockTimePassed($timeDiff)
    {
        $this->timeAdapter->shouldReceive('now')->andReturn(self::INITIAL_TIME + $timeDiff);
    }
}
