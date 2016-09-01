<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Hydrator;

use Sunspikes\Ratelimit\Throttle\Entity\Data;
use Sunspikes\Ratelimit\Throttle\Hydrator\ArrayHydrator;

class ArrayHydratorTest extends \PHPUnit_Framework_TestCase
{
    public function testHydrate()
    {
        $arrayHydrator = new ArrayHydrator();
        $data = $arrayHydrator->hydrate([]);

        $this->assertInstanceOf(Data::class, $data);
    }
}
