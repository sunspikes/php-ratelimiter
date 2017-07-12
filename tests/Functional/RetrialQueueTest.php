<?php

namespace Sunspikes\Tests\Functional;

use Mockery as M;
use Sunspikes\Ratelimit\Cache\Adapter\DesarrollaCacheAdapter;
use Sunspikes\Ratelimit\Cache\Factory\FactoryInterface;
use Sunspikes\Ratelimit\RateLimiter;
use Sunspikes\Ratelimit\Throttle\Factory\TimeAwareThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Hydrator\HydratorFactory;
use Sunspikes\Ratelimit\Throttle\Settings\FixedThrottleSettings;
use Sunspikes\Ratelimit\Throttle\Settings\RetrialQueueSettings;
use Sunspikes\Ratelimit\Throttle\Throttler\ThrottlerInterface;
use Sunspikes\Ratelimit\Time\TimeProviderInterface;

class RetrialQueueTest extends AbstractThrottlerTestCase
{
    const TIME_LIMIT = 24;

    /**
     * @var TimeProviderInterface|M\MockInterface
     */
    private $timeAdapter;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->timeAdapter = M::mock(TimeProviderInterface::class);
        $this->timeAdapter->shouldReceive('now')->andReturn(time());

        parent::setUp();
    }

    public function testThrottleAccess()
    {
        $this->timeAdapter->shouldReceive('usleep')
            ->with(
                ThrottlerInterface::SECOND_TO_MILLISECOND_MULTIPLIER *
                ThrottlerInterface::MILLISECOND_TO_MICROSECOND_MULTIPLIER *
                self::TIME_LIMIT
            )->once();

        parent::testThrottleAccess();
    }

    /**
     * @inheritdoc
     */
    protected function createRatelimiter(FactoryInterface $cacheFactory)
    {
        return new RateLimiter(
            new TimeAwareThrottlerFactory(new DesarrollaCacheAdapter($cacheFactory->make()), $this->timeAdapter),
            new HydratorFactory(),
            new RetrialQueueSettings(new FixedThrottleSettings($this->getMaxAttempts(), self::TIME_LIMIT))
        );
    }
}
