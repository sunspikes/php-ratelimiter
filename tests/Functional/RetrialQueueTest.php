<?php

namespace Sunspikes\Tests\Functional;

use Mockery as M;
use Sunspikes\Ratelimit\Cache\Adapter\DesarrollaCacheAdapter;
use Sunspikes\Ratelimit\Cache\Factory\FactoryInterface;
use Sunspikes\Ratelimit\RateLimiter;
use Sunspikes\Ratelimit\Throttle\Factory\TimeAwareThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Hydrator\HydratorFactory;
use Sunspikes\Ratelimit\Throttle\Settings\MovingWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\RetrialQueueSettings;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

class RetrialQueueTest extends AbstractThrottlerTestCase
{
    const TIME_LIMIT = 24;

    /**
     * @var TimeAdapterInterface|M\MockInterface
     */
    private $timeAdapter;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->timeAdapter = M::mock(TimeAdapterInterface::class);
        $this->timeAdapter->shouldReceive('now')->andReturn(time());

        parent::setUp();
    }

    public function testThrottleAccess()
    {
        $expectedWaitTime = self::TIME_LIMIT / $this->getMaxAttempts();
        $this->timeAdapter->shouldReceive('usleep')->with(1e6 * $expectedWaitTime)->once()->ordered();
        $this->timeAdapter->shouldReceive('usleep')->with(2 * 1e6 * $expectedWaitTime)->once()->ordered();

        $throttle = $this->ratelimiter->get('access-test');

        for ($i = 0; $i < $this->getMaxAttempts(); $i++) {
            $throttle->access();
        }

        $this->assertFalse($throttle->access());
        $this->assertFalse($throttle->access());
    }

    /**
     * @inheritdoc
     */
    protected function createRatelimiter(FactoryInterface $cacheFactory)
    {
        return new RateLimiter(
            new TimeAwareThrottlerFactory(new DesarrollaCacheAdapter($cacheFactory->make()), $this->timeAdapter),
            new HydratorFactory(),
            new RetrialQueueSettings(new MovingWindowSettings($this->getMaxAttempts(), self::TIME_LIMIT))
        );
    }
}
