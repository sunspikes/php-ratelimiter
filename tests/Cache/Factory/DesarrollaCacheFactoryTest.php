<?php

namespace Sunspikes\Tests\Ratelimit\Cache\Factory;

use Sunspikes\Ratelimit\Cache\Factory\DesarrollaCacheFactory;

class DesarrollaCacheFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $factory = new DesarrollaCacheFactory();
        $cache = $factory->make();

        $this->assertInstanceOf('\Desarrolla2\Cache\Cache', $cache);
    }
}
