<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Hydrator;

use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Throttle\Hydrator\HydratorFactory;
use Sunspikes\Ratelimit\Throttle\Hydrator\ArrayHydrator;
use Sunspikes\Ratelimit\Throttle\Hydrator\StringHydrator;

class HydratorFactoryTest extends TestCase
{
    /** @var HydratorFactory */
    private $hydratorFactory;

    public function setUp()
    {
        parent::setUp();

        $this->hydratorFactory = new HydratorFactory();
    }

    public function testArrayHydrator()
    {
        $hydrator = $this->hydratorFactory->make([]);

        $this->assertInstanceOf(ArrayHydrator::class, $hydrator);
    }

    public function testStringHydrator()
    {
        $hydrator = $this->hydratorFactory->make('test');

        $this->assertInstanceOf(StringHydrator::class, $hydrator);
    }

    /**
     * @expectedException \Sunspikes\Ratelimit\Throttle\Exception\InvalidDataTypeException
     */
    public function testUnsupportedHydrator()
    {
        $this->hydratorFactory->make(1);
    }
}