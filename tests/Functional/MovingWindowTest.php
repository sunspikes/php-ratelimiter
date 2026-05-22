<?php

namespace Sunspikes\Tests\Ratelimit\Functional;

use Mockery as M;
use Sunspikes\Ratelimit\Cache\Adapter\DesarrollaCacheAdapter;
use Sunspikes\Ratelimit\Cache\Factory\FactoryInterface;
use Sunspikes\Ratelimit\RateLimiter;
use Sunspikes\Ratelimit\Throttle\Factory\TimeAwareThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Hydrator\HydratorFactory;
use Sunspikes\Ratelimit\Throttle\Settings\MovingWindowSettings;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

class MovingWindowTest extends AbstractThrottlerTestCase
{
    const TIME_LIMIT = 24;

    private int $startTime;
    private TimeAdapterInterface|M\MockInterface $timeAdapter;

    protected function setUp(): void
    {
        $this->timeAdapter = M::mock(TimeAdapterInterface::class);
        $this->timeAdapter->shouldReceive('now')->andReturn($this->startTime = time())->byDefault();

        parent::setUp();
    }

    public function testWindowMoves(): void
    {
        $throttle = $this->ratelimiter->get('window-moves');

        $timeValues = [];

        for ($i = 0; $i < $this->getMaxAttempts(); $i++) {
            $timeValues[] = $this->startTime + $i;
            $timeValues[] = $this->startTime + $i;
        }

        $timeValues[] = $this->startTime + self::TIME_LIMIT + 1;
        $timeValues[] = $this->startTime + self::TIME_LIMIT + 1;

        $this->timeAdapter->shouldReceive('now')->andReturnValues($timeValues);

        for ($i = 0; $i < $this->getMaxAttempts() + 1; $i++) {
            $throttle->hit();
        }

        self::assertEquals($this->getMaxAttempts(), $throttle->count());
    }

    protected function createRatelimiter(FactoryInterface $cacheFactory): RateLimiter
    {
        return new RateLimiter(
            new TimeAwareThrottlerFactory(new DesarrollaCacheAdapter($cacheFactory->make()), $this->timeAdapter),
            new HydratorFactory(),
            new MovingWindowSettings($this->getMaxAttempts(), self::TIME_LIMIT)
        );
    }
}
