<?php

namespace Sunspikes\Tests\Ratelimit\Functional;

use Mockery as M;
use Sunspikes\Ratelimit\Cache\Adapter\DesarrollaCacheAdapter;
use Sunspikes\Ratelimit\Cache\Factory\FactoryInterface;
use Sunspikes\Ratelimit\RateLimiter;
use Sunspikes\Ratelimit\Throttle\Factory\TimeAwareThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Hydrator\HydratorFactory;
use Sunspikes\Ratelimit\Throttle\Settings\LeakyBucketSettings;
use Sunspikes\Ratelimit\Throttle\Throttler\ThrottlerInterface;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

class LeakyBucketTest extends AbstractThrottlerTestCase
{
    const TIME_LIMIT = 27000;
    const TOKEN_LIMIT = 30;

    private TimeAdapterInterface|M\MockInterface $timeAdapter;

    protected function setUp(): void
    {
        $this->timeAdapter = M::mock(TimeAdapterInterface::class);
        $this->timeAdapter->shouldReceive('now')->andReturn(time());

        parent::setUp();
    }

    public function testThrottleAccess(): void
    {
        $expectedWaitTime = self::TIME_LIMIT / (self::TOKEN_LIMIT - $this->getMaxAttempts());
        $this->timeAdapter->shouldReceive('usleep')
            ->with(ThrottlerInterface::MILLISECOND_TO_MICROSECOND_MULTIPLIER * $expectedWaitTime)
            ->once();

        parent::testThrottleAccess();
    }

    protected function createRatelimiter(FactoryInterface $cacheFactory): RateLimiter
    {
        return new RateLimiter(
            new TimeAwareThrottlerFactory(new DesarrollaCacheAdapter($cacheFactory->make()), $this->timeAdapter),
            new HydratorFactory(),
            new LeakyBucketSettings(self::TOKEN_LIMIT, self::TIME_LIMIT, $this->getMaxAttempts())
        );
    }
}
