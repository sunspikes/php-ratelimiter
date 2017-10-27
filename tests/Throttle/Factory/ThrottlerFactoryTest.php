<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Factory;

use Mockery as M;
use Mockery\MockInterface;
use Sunspikes\Ratelimit\Cache\ThrottlerCacheInterface;
use Sunspikes\Ratelimit\Throttle\Entity\Data;
use Sunspikes\Ratelimit\Throttle\Factory\ThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Settings\ElasticWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;
use Sunspikes\Ratelimit\Throttle\Throttler\ElasticWindowThrottler;
use Sunspikes\Ratelimit\Throttle\Factory\FactoryInterface;

class ThrottlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ThrottlerCacheInterface|MockInterface
     */
    protected $throttlerCache;

    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->throttlerCache = M::mock(ThrottlerCacheInterface::class);
        $this->factory = new ThrottlerFactory($this->throttlerCache);
    }

    public function testMakeElasticWindow()
    {
        self::assertInstanceOf(
            ElasticWindowThrottler::class,
            $this->factory->make($this->getData(), new ElasticWindowSettings(3, 600))
        );
    }

    public function testInvalidSettings()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $this->factory->make($this->getData(), new ElasticWindowSettings());
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
