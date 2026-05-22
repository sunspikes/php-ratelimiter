<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Hydrator;

use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Throttle\Exception\InvalidDataTypeException;
use Sunspikes\Ratelimit\Throttle\Hydrator\ArrayHydrator;
use Sunspikes\Ratelimit\Throttle\Hydrator\HydratorFactory;
use Sunspikes\Ratelimit\Throttle\Hydrator\StringHydrator;

class HydratorFactoryTest extends TestCase
{
    private HydratorFactory $hydratorFactory;

    protected function setUp(): void
    {
        $this->hydratorFactory = new HydratorFactory();
    }

    public function testArrayHydrator(): void
    {
        $hydrator = $this->hydratorFactory->make([]);

        $this->assertInstanceOf(ArrayHydrator::class, $hydrator);
    }

    public function testStringHydrator(): void
    {
        $hydrator = $this->hydratorFactory->make('test');

        $this->assertInstanceOf(StringHydrator::class, $hydrator);
    }

    public function testUnsupportedHydrator(): void
    {
        $this->expectException(InvalidDataTypeException::class);
        $this->hydratorFactory->make(1);
    }
}
