<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Factory;

use Mockery as M;
use Sunspikes\Ratelimit\Throttle\Factory\ThrottlerFactory;

class ThrottlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $dataMock = M::mock('\Sunspikes\Ratelimit\Throttle\Entity\Data');
        $dataMock->shouldReceive('getKey')
                 ->andReturn('getKey')
                 ->once();
        $dataMock->shouldReceive('getLimit')
                 ->andReturn(3)
                 ->once();
        $dataMock->shouldReceive('getTtl')
                 ->andReturn(600)
                 ->once();

        $adapterMock = M::mock('\Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface');

        $factory = new ThrottlerFactory();
        $throttler = $factory->make($dataMock, $adapterMock);

        $this->assertInstanceOf('\Sunspikes\Ratelimit\Throttle\Throttler\CacheThrottler', $throttler);
    }
}