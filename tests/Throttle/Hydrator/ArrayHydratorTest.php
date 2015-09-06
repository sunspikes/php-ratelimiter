<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Hydrator;

use Sunspikes\Ratelimit\Throttle\Hydrator\ArrayHydrator;

class ArrayHydratorTest extends \PHPUnit_Framework_TestCase
{
    public function testHydrate()
    {
        $arrayHydrator = new ArrayHydrator();
        $data = $arrayHydrator->hydrate([], 3, 600);

        $this->assertInstanceOf('\Sunspikes\Ratelimit\Throttle\Entity\Data', $data);
    }
}