<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Factory;

use Mockery as M;
use Mockery\MockInterface;
use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Throttle\Factory\TimeAwareThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Settings\FixedThrottleSettings;
use Sunspikes\Ratelimit\Throttle\Settings\LeakyBucketSettings;
use Sunspikes\Ratelimit\Throttle\Settings\MovingThrottleSettings;
use Sunspikes\Ratelimit\Throttle\Settings\RetrialQueueSettings;
use Sunspikes\Ratelimit\Throttle\Throttler\FixedWindowThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\LeakyBucketThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\MovingWindowThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\RetrialQueueThrottler;
use Sunspikes\Ratelimit\Time\TimeProviderInterface;

class TimeAwareThrottlerFactoryTest extends ThrottlerFactoryTest
{
    /**
     * @var CacheAdapterInterface|MockInterface
     */
    protected $cacheAdapter;

    /**
     * @var TimeProviderInterface|MockInterface
     */
    private $timeAdapter;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->timeAdapter = M::mock(TimeProviderInterface::class);
        $this->cacheAdapter = M::mock(CacheAdapterInterface::class);

        $this->factory = new TimeAwareThrottlerFactory($this->cacheAdapter, $this->timeAdapter);
    }

    public function testMakeLeakyBucket()
    {
        self::assertInstanceOf(
            LeakyBucketThrottler::class,
            $this->factory->make($this->getData(), new LeakyBucketSettings(120, 60))
        );
    }

    public function testMakeMovingWindow()
    {
        self::assertInstanceOf(
            MovingWindowThrottler::class,
            $this->factory->make($this->getData(), new MovingThrottleSettings(120, 60))
        );
    }

    public function testMakeFixedWindow()
    {
        self::assertInstanceOf(
            FixedWindowThrottler::class,
            $this->factory->make($this->getData(), new FixedThrottleSettings(120, 60))
        );
    }

    public function testMakeRetrialQueue()
    {
        self::assertInstanceOf(
            RetrialQueueThrottler::class,
            $this->factory->make(
                $this->getData(),
                new RetrialQueueSettings(new MovingThrottleSettings(120, 60))
            )
        );
    }
}
