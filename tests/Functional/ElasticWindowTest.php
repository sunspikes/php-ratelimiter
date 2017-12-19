<?php

namespace Sunspikes\Tests\Ratelimit\Functional;

use Mockery as M;
use Sunspikes\Ratelimit\Cache\ThrottlerCacheInterface;
use Sunspikes\Ratelimit\RateLimiter;
use Sunspikes\Ratelimit\Throttle\Factory\ThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Hydrator\HydratorFactory;
use Sunspikes\Ratelimit\Throttle\Settings\ElasticWindowSettings;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

class ElasticWindowTest extends AbstractThrottlerTestCase
{
    /**
     * @var TimeAdapterInterface|M\MockInterface
     */
    private $timeAdapter;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->timeAdapter = M::mock(TimeAdapterInterface::class);
        $this->timeAdapter->shouldReceive('now')->andReturn(time())->byDefault();

        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function createRatelimiter(ThrottlerCacheInterface $throttlerCache)
    {
        return new RateLimiter(
            new ThrottlerFactory($throttlerCache, $this->timeAdapter),
            new HydratorFactory(),
            new ElasticWindowSettings($this->getMaxAttempts(), 600)
        );
    }
}
