<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Hydrator;

use Sunspikes\Ratelimit\Throttle\Hydrator\HydratorFactory;

class HydratorFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $hydratorFactory;

    public function setUp()
    {
        parent::setUp();

        $this->hydratorFactory = new HydratorFactory();
    }

    public function testArrayHydrator()
    {
        $hydrator = $this->hydratorFactory->make([]);

        $this->assertInstanceOf('\Sunspikes\Ratelimit\Throttle\Hydrator\ArrayHydrator', $hydrator);
    }

    public function testStringHydrator()
    {
        $hydrator = $this->hydratorFactory->make('test');

        $this->assertInstanceOf('\Sunspikes\Ratelimit\Throttle\Hydrator\StringHydrator', $hydrator);
    }

    /**
     * @expectedException \Sunspikes\Ratelimit\Throttle\Exception\InvalidDataTypeException
     */
    public function testUnsupportedHydrator()
    {
        $hydrator = $this->hydratorFactory->make(1);
    }
}