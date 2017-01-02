<?php

namespace Sunspikes\Tests\Ratelimit\Cache\Factory;

use Desarrolla2\Cache\Cache;
use Sunspikes\Ratelimit\Cache\Factory\DesarrollaCacheFactory;

class DesarrollaCacheFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMake()
    {
        $factory = new DesarrollaCacheFactory();
        $cache = $factory->make();

        $this->assertInstanceOf(Cache::class, $cache);
    }

    /**
     * @dataProvider configProvider
     *
     * @param array $config
     */
    public function testCreateDrivers(array $config, $driverClass)
    {
        if (null !== $driverClass && !class_exists($driverClass)) {
            $this->markTestSkipped($driverClass.' is not available on this system');
        }

        $factory = new DesarrollaCacheFactory(null, $config);
        $this->assertInstanceOf(Cache::class, $factory->make());
    }

    /**
     * @return array
     */
    public function configProvider()
    {
        return [
            [['driver' => 'file'], null],
            [['driver' => 'apc'], null],
            [['driver' => 'memory'], null],
            [['driver' => 'mongo'], \MongoClient::class],
            [['driver' => 'redis'], \Predis\Client::class],
            [['driver' => 'mysql', 'mysql' => []], \mysqli::class],
            [['driver' => 'memcache'], \Memcache::class],
        ];
    }
}
