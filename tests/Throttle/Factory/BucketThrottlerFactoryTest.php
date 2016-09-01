<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Factory;

use Mockery as M;
use Mockery\MockInterface;
use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Throttle\Factory\BucketThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Settings\LeakyBucketSettings;
use Sunspikes\Ratelimit\Throttle\Throttler\LeakyBucketThrottler;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

class BucketThrottlerFactoryTest extends ThrottlerFactoryTest
{
    /**
     * @var CacheAdapterInterface|MockInterface
     */
    protected $cacheAdapter;

    /**
     * @var TimeAdapterInterface|MockInterface
     */
    private $timeAdapter;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->timeAdapter = M::mock(TimeAdapterInterface::class);
        $this->cacheAdapter = M::mock(CacheAdapterInterface::class);

        $this->factory = new BucketThrottlerFactory($this->cacheAdapter, $this->timeAdapter);
    }

    public function testMakeLeakyBucket()
    {
        self::assertInstanceOf(
            LeakyBucketThrottler::class,
            $this->factory->make($this->getData(), new LeakyBucketSettings(120, 60))
        );
    }
}
