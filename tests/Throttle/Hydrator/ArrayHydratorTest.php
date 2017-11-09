<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Hydrator;

use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Throttle\Entity\Data;
use Sunspikes\Ratelimit\Throttle\Hydrator\ArrayHydrator;

class ArrayHydratorTest extends TestCase
{
    public function testHydrate()
    {
        $arrayHydrator = new ArrayHydrator();
        $data = $arrayHydrator->hydrate([]);

        $this->assertInstanceOf(Data::class, $data);
    }
}
