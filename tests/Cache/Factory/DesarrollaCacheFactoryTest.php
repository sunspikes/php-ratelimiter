<?php

namespace Sunspikes\Tests\Ratelimit\Cache\Factory;

use Desarrolla2\Cache\CacheInterface;
use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Cache\Exception\InvalidConfigException;
use Sunspikes\Ratelimit\Cache\Factory\DesarrollaCacheFactory;

class DesarrollaCacheFactoryTest extends TestCase
{
    public function testMake(): void
    {
        $factory = new DesarrollaCacheFactory();
        $cache = $factory->make();

        $this->assertInstanceOf(CacheInterface::class, $cache);
    }

    /**
     * @dataProvider configProvider
     */
    public function testCreateDrivers(array $config, ?string $driverClass): void
    {
        if (null !== $driverClass && !class_exists($driverClass)) {
            $this->markTestSkipped($driverClass . ' is not available on this system');
        }

        $factory = new DesarrollaCacheFactory(null, $config);
        $this->assertInstanceOf(CacheInterface::class, $factory->make());
    }

    public function testMysqlRequiresConfig(): void
    {
        $this->expectException(InvalidConfigException::class);
        $factory = new DesarrollaCacheFactory(null, ['driver' => 'mysql', 'mysql' => []]);
        $factory->make();
    }

    public static function configProvider(): array
    {
        return [
            [['driver' => 'file'], null],
            [['driver' => 'apc'], null],
            [['driver' => 'memory'], null],
            [['driver' => 'mongo'], \MongoDB\Client::class],
            [['driver' => 'redis'], \Predis\Client::class],
            [['driver' => 'memcache'], \Memcached::class],
        ];
    }
}
