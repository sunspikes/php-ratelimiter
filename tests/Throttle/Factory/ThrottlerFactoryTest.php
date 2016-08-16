<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Factory;

use Mockery;
use Mockery\MockInterface;
use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Throttle\Entity\Data;
use Sunspikes\Ratelimit\Throttle\Factory\ThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Settings\FixedWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\LeakyBucketSettings;
use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;
use Sunspikes\Ratelimit\Throttle\Throttler\CacheThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\LeakyBucketThrottler;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

class ThrottlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CacheAdapterInterface|MockInterface
     */
    private $cacheAdapter;

    /**
     * @var TimeAdapterInterface|MockInterface
     */
    private $timeAdapter;

    /**
     * @var ThrottlerFactory
     */
    private $factory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->cacheAdapter = Mockery::mock(CacheAdapterInterface::class);
        $this->timeAdapter = Mockery::mock(TimeAdapterInterface::class);

        $this->factory = new ThrottlerFactory($this->cacheAdapter, $this->timeAdapter);
    }

    public function testMakeFixedWindow()
    {
        self::assertInstanceOf(
            CacheThrottler::class,
            $this->factory->make($this->getData(), new FixedWindowSettings(3, 600))
        );
    }

    public function testMakeLeakyBucket()
    {
        self::assertInstanceOf(
            LeakyBucketThrottler::class,
            $this->factory->make($this->getData(), new LeakyBucketSettings(120, 60))
        );
    }

    public function testInvalidSettings()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $this->factory->make($this->getData(), new FixedWindowSettings());
    }

    public function testUnknownSettings()
    {
        $settings = Mockery::mock(ThrottleSettingsInterface::class);
        $settings->shouldReceive('isValid')->andReturn(true);

        $this->setExpectedException(\InvalidArgumentException::class);
        $this->factory->make($this->getData(), $settings);
    }

    /**
     * @return Data
     */
    private function getData()
    {
        return new Data('someKey');
    }
}
