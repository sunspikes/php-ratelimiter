<?php

namespace Sunspikes\Tests\Ratelimit\Functional;

use Mockery as M;
use Sunspikes\Ratelimit\Cache\Adapter\DesarrollaCacheAdapter;
use Sunspikes\Ratelimit\Cache\Factory\FactoryInterface;
use Sunspikes\Ratelimit\RateLimiter;
use Sunspikes\Ratelimit\Throttle\Factory\TimeAwareThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Hydrator\HydratorFactory;
use Sunspikes\Ratelimit\Throttle\Settings\FixedWindowSettings;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

class FixedWindowTest extends AbstractThrottlerTestCase
{
    const TIME_LIMIT = 4;

    private int $startTime;
    private TimeAdapterInterface|M\MockInterface $timeAdapter;

    protected function setUp(): void
    {
        $this->timeAdapter = M::mock(TimeAdapterInterface::class);
        $this->timeAdapter->shouldReceive('now')->andReturn($this->startTime = time())->byDefault();

        parent::setUp();
    }

    public function testWindowIsFixed(): void
    {
        $throttle = $this->ratelimiter->get('window-is-fixed');

        for ($i = -1; $i < $this->getMaxAttempts(); $i++) {
            $throttle->hit();
        }

        $this->timeAdapter->shouldReceive('now')->andReturn($this->startTime + self::TIME_LIMIT + 1);

        self::assertEquals(0, $throttle->count());
    }

    protected function createRatelimiter(FactoryInterface $cacheFactory): RateLimiter
    {
        return new RateLimiter(
            new TimeAwareThrottlerFactory(new DesarrollaCacheAdapter($cacheFactory->make()), $this->timeAdapter),
            new HydratorFactory(),
            new FixedWindowSettings($this->getMaxAttempts(), self::TIME_LIMIT)
        );
    }
}
