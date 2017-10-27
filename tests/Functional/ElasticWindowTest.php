<?php

namespace Sunspikes\Tests\Ratelimit\Functional;

use Sunspikes\Ratelimit\Cache\ThrottlerCacheInterface;
use Sunspikes\Ratelimit\RateLimiter;
use Sunspikes\Ratelimit\Throttle\Factory\ThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Hydrator\HydratorFactory;
use Sunspikes\Ratelimit\Throttle\Settings\ElasticWindowSettings;

class ElasticWindowTest extends AbstractThrottlerTestCase
{
    /**
     * @inheritdoc
     */
    protected function createRatelimiter(ThrottlerCacheInterface $throttlerCache)
    {
        return new RateLimiter(
            new ThrottlerFactory($throttlerCache),
            new HydratorFactory(),
            new ElasticWindowSettings($this->getMaxAttempts(), 600)
        );
    }
}
