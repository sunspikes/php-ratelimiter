<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Hydrator;

use Sunspikes\Ratelimit\Throttle\Hydrator\StringHydrator;

class StringHydratorTest extends \PHPUnit_Framework_TestCase
{
    public function testHydrate()
    {
        $stringHydrator = new StringHydrator();
        $data = $stringHydrator->hydrate('test', 3, 600);

        $this->assertInstanceOf('\Sunspikes\Ratelimit\Throttle\Entity\Data', $data);
    }
}