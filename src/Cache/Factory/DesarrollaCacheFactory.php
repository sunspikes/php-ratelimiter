<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Krishnaprasad MG <sunspikes@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Sunspikes\Ratelimit\Cache\Factory;

use Desarrolla2\Cache\Apcu;
use Desarrolla2\Cache\CacheInterface;
use Desarrolla2\Cache\File;
use Desarrolla2\Cache\Memcached;
use Desarrolla2\Cache\Memory;
use Desarrolla2\Cache\MongoDB;
use Desarrolla2\Cache\Mysqli;
use Desarrolla2\Cache\NotCache;
use Desarrolla2\Cache\Predis;
use Sunspikes\Ratelimit\Cache\Exception\DriverNotFoundException;
use Sunspikes\Ratelimit\Cache\Exception\InvalidConfigException;

class DesarrollaCacheFactory implements FactoryInterface
{
    public const DEFAULT_TTL = 3600;
    public const DEFAULT_LIMIT = 1000;

    protected array $config;

    /**
     * @param string|null $configFile
     * @param array       $configArray
     */
    public function __construct(?string $configFile = null, array $configArray = [])
    {
        // Default config from distribution
        if (null === $configFile) {
            $configFile = __DIR__.'/../../../config/config.php';
        }

        $config = include $configFile;

        $this->config = array_merge($config, $configArray);
    }

    /**
     * @inheritdoc
     */
    public function make(): CacheInterface
    {
        return $this->createDriver();
    }

    /**
     * Make the driver based on given config
     *
     * @return CacheInterface
     *
     * @throws DriverNotFoundException
     * @throws InvalidConfigException
     */
    protected function createDriver(): CacheInterface
    {
        $driver = $this->config['driver'] ?? null;

        if (null === $driver) {
            throw new InvalidConfigException('Cache driver is not defined in configuration.');
        }

        $adapter = match (strtolower((string) $driver)) {
            'notcache' => $this->createNotcacheDriver(),
            'file' => $this->createFileDriver(),
            'apc', 'apcu' => $this->createApcDriver(),
            'memory' => $this->createMemoryDriver(),
            'mongo', 'mongodb' => $this->createMongoDriver(),
            'mysql', 'mysqli' => $this->createMysqlDriver(),
            'redis' => $this->createRedisDriver(),
            'memcache' => $this->createMemcacheDriver(),
            default => throw new DriverNotFoundException('Cannot find the driver ' . $driver . ' for Desarrolla')
        };

        return $adapter->withOption('ttl', $this->config['default_ttl'] ?? self::DEFAULT_TTL);
    }

    /**
     * Create NotCache driver
     *
     * @return NotCache
     */
    protected function createNotcacheDriver(): NotCache
    {
        return new NotCache();
    }

    /**
     * Create File driver
     *
     * @return File
     */
    protected function createFileDriver(): File
    {
        return new File($this->config['file']['cache_dir'] ?? sys_get_temp_dir());
    }

    /**
     * Create APC driver
     *
     * @return Apcu
     */
    protected function createApcDriver(): Apcu
    {
        return new Apcu();
    }

    /**
     * Create Memory driver
     *
     * @return Memory
     */
    protected function createMemoryDriver(): Memory
    {
        return (new Memory())->withOption('limit', $this->config['memory']['limit'] ?? self::DEFAULT_LIMIT);
    }

    /**
     * Create Mongo driver
     *
     * @return Mongo
     */
    protected function createMongoDriver(): MongoDB
    {
        return new MongoDB($this->config['mongo']['server']);
    }

    /**
     * Create MySQL driver
     *
     * @return Mysqli
     */
    protected function createMysqlDriver(): Mysqli
    {
        if (empty($this->config['mysql'])) {
            throw new InvalidConfigException('MySQL configuration is required for the mysql driver.');
        }

        $server = new \mysqli(
            $this->config['mysql']['host'],
            $this->config['mysql']['username'],
            $this->config['mysql']['password'],
            $this->config['mysql']['dbname'],
            (int) $this->config['mysql']['port']
        );

        return new Mysqli($server);
    }

    /**
     * Create Redis driver
     *
     * @return Predis
     */
    protected function createRedisDriver(): Predis
    {
        return new Predis();
    }

    /**
     * Create Memcached driver
     *
     * @return Memcached
     */
    protected function createMemcacheDriver(): Memcached
    {
        $server = null;

        if (isset($this->config['memcache']['servers'])) {
            $server = new \Memcached();

            foreach ($this->config['memcache']['servers'] as $host) {
                $server->addServer($host, 11211);
            }
        }

        return new Memcached($server);
    }
}
