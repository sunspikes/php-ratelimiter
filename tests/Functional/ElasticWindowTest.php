<?php

namespace Sunspikes\Tests\Functional;

use Sunspikes\Ratelimit\Cache\Adapter\DesarrollaCacheAdapter;
use Sunspikes\Ratelimit\Cache\Factory\FactoryInterface;
use Sunspikes\Ratelimit\RateLimiter;
use Sunspikes\Ratelimit\Throttle\Factory\ThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Hydrator\HydratorFactory;
use Sunspikes\Ratelimit\Throttle\Settings\ElasticWindowSettings;

class ElasticWindowTest extends AbstractThrottlerTestCase
{
    /**
     * @inheritdoc
     */
    protected function createRatelimiter(FactoryInterface $cacheFactory)
    {
        return new RateLimiter(
            new ThrottlerFactory(new DesarrollaCacheAdapter($cacheFactory->make())),
            new HydratorFactory(),
            new ElasticWindowSettings($this->getMaxAttempts(), 600)
        );
    }
}
