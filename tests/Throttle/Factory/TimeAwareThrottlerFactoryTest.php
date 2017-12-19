<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Factory;

use Mockery as M;
use Mockery\MockInterface;
use Sunspikes\Ratelimit\Cache\ThrottlerCacheInterface;
use Sunspikes\Ratelimit\Throttle\Factory\TimeAwareThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Settings\FixedWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\LeakyBucketSettings;
use Sunspikes\Ratelimit\Throttle\Settings\MovingWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\RetrialQueueSettings;
use Sunspikes\Ratelimit\Throttle\Throttler\FixedWindowThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\LeakyBucketThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\MovingWindowThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\RetrialQueueThrottler;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

class TimeAwareThrottlerFactoryTest extends ThrottlerFactoryTest
{
    /**
     * @var ThrottlerCacheInterface|MockInterface
     */
    protected $throttlerCache;

    /**
     * @var TimeAdapterInterface|MockInterface
     */
    private $timeAdapter;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->timeAdapter = M::mock(TimeAdapterInterface::class);
        $this->throttlerCache = M::mock(ThrottlerCacheInterface::class);

        $this->factory = new TimeAwareThrottlerFactory($this->throttlerCache, $this->timeAdapter);
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
            $this->factory->make($this->getData(), new MovingWindowSettings(120, 60))
        );
    }

    public function testMakeFixedWindow()
    {
        self::assertInstanceOf(
            FixedWindowThrottler::class,
            $this->factory->make($this->getData(), new FixedWindowSettings(120, 60))
        );
    }

    public function testMakeRetrialQueue()
    {
        self::assertInstanceOf(
            RetrialQueueThrottler::class,
            $this->factory->make(
                $this->getData(),
                new RetrialQueueSettings(new MovingWindowSettings(120, 60))
            )
        );
    }
}
