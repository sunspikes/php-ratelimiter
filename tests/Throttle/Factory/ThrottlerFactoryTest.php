<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Factory;

use Mockery as M;
use Mockery\MockInterface;
use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Throttle\Entity\Data;
use Sunspikes\Ratelimit\Throttle\Factory\ThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Settings\FixedWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;
use Sunspikes\Ratelimit\Throttle\Throttler\CacheThrottler;
use Sunspikes\Ratelimit\Throttle\Factory\FactoryInterface;

class ThrottlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CacheAdapterInterface|MockInterface
     */
    protected $cacheAdapter;

    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->cacheAdapter = M::mock(CacheAdapterInterface::class);
        $this->factory = new ThrottlerFactory($this->cacheAdapter);
    }

    public function testMakeFixedWindow()
    {
        self::assertInstanceOf(
            CacheThrottler::class,
            $this->factory->make($this->getData(), new FixedWindowSettings(3, 600))
        );
    }

    public function testInvalidSettings()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $this->factory->make($this->getData(), new FixedWindowSettings());
    }

    public function testUnknownSettings()
    {
        $settings = M::mock(ThrottleSettingsInterface::class);
        $settings->shouldReceive('isValid')->andReturn(true);

        $this->setExpectedException(\InvalidArgumentException::class);
        $this->factory->make($this->getData(), $settings);
    }

    /**
     * @return Data
     */
    protected function getData()
    {
        return new Data('someKey');
    }
}
