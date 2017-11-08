<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Hydrator;

use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Throttle\Entity\Data;
use Sunspikes\Ratelimit\Throttle\Hydrator\StringHydrator;

class StringHydratorTest extends TestCase
{
    public function testHydrate()
    {
        $stringHydrator = new StringHydrator();
        $data = $stringHydrator->hydrate('test');

        $this->assertInstanceOf(Data::class, $data);
    }
}
