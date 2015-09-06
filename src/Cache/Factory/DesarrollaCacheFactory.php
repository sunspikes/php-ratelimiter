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

namespace Sunspikes\Ratelimit\Cache\Factory;

use Desarrolla2\Cache\Adapter\Apc;
use Desarrolla2\Cache\Adapter\File;
use Desarrolla2\Cache\Adapter\MemCache;
use Desarrolla2\Cache\Adapter\Memory;
use Desarrolla2\Cache\Adapter\Mongo;
use Desarrolla2\Cache\Adapter\MySQL;
use Desarrolla2\Cache\Adapter\NotCache;
use Desarrolla2\Cache\Adapter\Redis;
use Desarrolla2\Cache\Cache;
use Sunspikes\Ratelimit\Cache\Exception\DriverNotFoundException;
use Sunspikes\Ratelimit\Cache\Exception\InvalidConfigException;

class DesarrollaCacheFactory implements FactoryInterface
{
    /* @const DEFAULT_TTL */
    const DEFAULT_TTL = 3600;
    /* @const DEFAULT_LIMIT */
    const DEFAULT_LIMIT = 1000;

    /* @var array */
    protected $config;

    /**
     * @inheritdoc
     */
    public function make($config)
    {
        $this->config = $config['desarrolla'];
        $driver = $this->getDriver();

        return new Cache($driver);
    }

    /**
     * Make the driver based on given config
     *
     * @return null|\Desarrolla2\Cache\Adapter\AdapterInterface
     * @throws DriverNotFoundException
     * @throws InvalidConfigException
     */
    protected function getDriver()
    {
        $driver = $this->config['driver'];

        if (is_null($driver)) {
            throw new InvalidConfigException('Cache driver is not defined in configuration.');
        }

        $driverCreateMethod = 'create'.ucfirst($driver).'Driver';

        if (method_exists($this, $driverCreateMethod)) {
            $driver = $this->{$driverCreateMethod}();
            $driver->setOption('ttl',
                $this->config['default_ttl']
                    ?: static::DEFAULT_TTL
            );

            return $driver;
        }

        throw new DriverNotFoundException('Cannot find the driver '.$driver.' for Desarrolla');
    }

    /**
     * Create NotCache driver
     *
     * @return NotCache
     */
    protected function createNotcacheDriver()
    {
        return new NotCache();
    }

    /**
     * Create File driver
     *
     * @return File
     */
    protected function createFileDriver()
    {
        return new File($this->config['cache_dir']);
    }

    /**
     * Create APC driver
     *
     * @return Apc
     */
    protected function createApcDriver()
    {
        return new Apc();
    }

    /**
     * Create Memory driver
     *
     * @return Memory
     * @throws \Desarrolla2\Cache\Adapter\MemoryCacheException
     */
    protected function createMemoryDriver()
    {
        $memory = new Memory();
        $memory->setOption('limit',
            $this->config['limit']
                ?: static::DEFAULT_LIMIT
        );

        return $memory;
    }

    /**
     * Create Mongo driver
     *
     * @return Mongo
     */
    protected function createMongoDriver()
    {
        return new Mongo($this->config['server']);
    }

    /**
     * Create MySQL driver
     *
     * @return MySQL
     */
    protected function createMysqlDriver()
    {
        return new MySQL(
            $this->config['host'],
            $this->config['username'],
            $this->config['password'],
            $this->config['port']
        );
    }

    /**
     * Create Redis driver
     *
     * @return Redis
     */
    protected function createRedisDriver()
    {
        return new Redis();
    }

    /**
     * Create MemCache driver
     *
     * @return MemCache
     */
    protected function createMemcacheDriver()
    {
        return new MemCache();
    }
}
