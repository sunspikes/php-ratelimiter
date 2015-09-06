<?php

namespace Sunspikes\Tests\Ratelimit\Cache\Factory;

use Sunspikes\Ratelimit\Cache\Factory\DesarrollaCacheFactory;

class DesarrollaCacheFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $config = [
            'adapter' => 'desarrolla',
            'desarrolla' => [
                'driver' => 'notcache',
                'default_ttl' => 3600,
                'notcache' => [],
            ],
        ];

        $factory = new DesarrollaCacheFactory();
        $cache = $factory->make($config);

        $this->assertInstanceOf('\Desarrolla2\Cache\Cache', $cache);
    }
}