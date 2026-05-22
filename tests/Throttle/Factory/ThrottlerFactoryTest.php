<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Factory;

use Mockery as M;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Throttle\Entity\Data;
use Sunspikes\Ratelimit\Throttle\Factory\ThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Factory\FactoryInterface;
use Sunspikes\Ratelimit\Throttle\Settings\ElasticWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;
use Sunspikes\Ratelimit\Throttle\Throttler\ElasticWindowThrottler;

class ThrottlerFactoryTest extends TestCase
{
    protected CacheAdapterInterface|MockInterface $cacheAdapter;
    protected FactoryInterface $factory;

    protected function setUp(): void
    {
        $this->cacheAdapter = M::mock(CacheAdapterInterface::class);
        $this->factory = new ThrottlerFactory($this->cacheAdapter);
    }

    public function testMakeElasticWindow(): void
    {
        self::assertInstanceOf(
            ElasticWindowThrottler::class,
            $this->factory->make($this->getData(), new ElasticWindowSettings(3, 600))
        );
    }

    public function testInvalidSettings(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->factory->make($this->getData(), new ElasticWindowSettings());
    }

    public function testUnknownSettings(): void
    {
        $settings = M::mock(ThrottleSettingsInterface::class);
        $settings->shouldReceive('isValid')->andReturn(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->factory->make($this->getData(), $settings);
    }

    protected function getData(): Data
    {
        return new Data('someKey');
    }
}
