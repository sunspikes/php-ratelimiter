<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Factory;

use Mockery as M;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Cache\ThrottlerCacheInterface;
use Sunspikes\Ratelimit\Throttle\Entity\Data;
use Sunspikes\Ratelimit\Throttle\Factory\ThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Settings\ElasticWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;
use Sunspikes\Ratelimit\Throttle\Throttler\ElasticWindowThrottler;
use Sunspikes\Ratelimit\Throttle\Factory\FactoryInterface;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

class ThrottlerFactoryTest extends TestCase
{
    /**
     * @var TimeAdapterInterface|MockInterface
     */
    protected $timeAdapter;

    /**
     * @var ThrottlerCacheInterface|MockInterface
     */
    protected $throttlerCache;

    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->timeAdapter = M::mock(TimeAdapterInterface::class);
        $this->throttlerCache = M::mock(ThrottlerCacheInterface::class);
        $this->factory = new ThrottlerFactory($this->throttlerCache, $this->timeAdapter);
    }

    public function testMakeElasticWindow()
    {
        self::assertInstanceOf(
            ElasticWindowThrottler::class,
            $this->factory->make($this->getData(), new ElasticWindowSettings(3, 600))
        );
    }

    /**
     * @expectedException \Sunspikes\Ratelimit\Throttle\Exception\InvalidThrottlerSettingsException
     */
    public function testInvalidSettings()
    {
        $this->factory->make($this->getData(), new ElasticWindowSettings());
    }

    /**
     * @expectedException \Sunspikes\Ratelimit\Throttle\Exception\InvalidThrottlerSettingsException
     */
    public function testUnknownSettings()
    {
        $settings = M::mock(ThrottleSettingsInterface::class);
        $settings->shouldReceive('isValid')->andReturn(true);

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
