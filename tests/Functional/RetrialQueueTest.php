<?php

namespace Sunspikes\Tests\Ratelimit\Functional;

use Mockery as M;
use Sunspikes\Ratelimit\Cache\Adapter\DesarrollaCacheAdapter;
use Sunspikes\Ratelimit\Cache\Factory\FactoryInterface;
use Sunspikes\Ratelimit\RateLimiter;
use Sunspikes\Ratelimit\Throttle\Factory\TimeAwareThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Hydrator\HydratorFactory;
use Sunspikes\Ratelimit\Throttle\Settings\FixedWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\RetrialQueueSettings;
use Sunspikes\Ratelimit\Throttle\Throttler\ThrottlerInterface;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

class RetrialQueueTest extends AbstractThrottlerTestCase
{
    const TIME_LIMIT = 24;

    private TimeAdapterInterface|M\MockInterface $timeAdapter;

    protected function setUp(): void
    {
        $this->timeAdapter = M::mock(TimeAdapterInterface::class);
        $this->timeAdapter->shouldReceive('now')->andReturn(time());

        parent::setUp();
    }

    public function testThrottleAccess(): void
    {
        $this->timeAdapter->shouldReceive('usleep')
            ->with(
                ThrottlerInterface::SECOND_TO_MILLISECOND_MULTIPLIER *
                ThrottlerInterface::MILLISECOND_TO_MICROSECOND_MULTIPLIER *
                self::TIME_LIMIT
            )->once();

        parent::testThrottleAccess();
    }

    protected function createRatelimiter(FactoryInterface $cacheFactory): RateLimiter
    {
        return new RateLimiter(
            new TimeAwareThrottlerFactory(new DesarrollaCacheAdapter($cacheFactory->make()), $this->timeAdapter),
            new HydratorFactory(),
            new RetrialQueueSettings(new FixedWindowSettings($this->getMaxAttempts(), self::TIME_LIMIT))
        );
    }
}
