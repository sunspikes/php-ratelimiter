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

namespace Sunspikes\Ratelimit;

use Sunspikes\Ratelimit\Cache\Adapter\DesarrollaCacheAdapter;
use Sunspikes\Ratelimit\Cache\Factory\DesarrollaCacheFactory;
use Sunspikes\Ratelimit\Throttle\Factory\FactoryInterface as ThrottlerFactoryInterface;
use Sunspikes\Ratelimit\Throttle\Hydrator\HydratorFactory as HydratorFactory;

class RateLimiter
{
    /**
     * @var \Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface
     */
    protected $adapter;

    /**
     * @var \Sunspikes\Ratelimit\Throttle\Throttler\ThrottlerInterface[]
     */
    protected $throttlers;

    /* @var int */
    protected $limit;

    /* @var int */
    protected $ttl;

    /**
     * @var ThrottlerFactoryInterface
     */
    protected $throttlerFactory;

    /**
     * @var HydratorFactory
     */
    protected $hydratorFactory;

    /**
     * @param ThrottlerFactoryInterface $throttlerFactory,
     * @param HydratorFactory           $hydratorFactory,
     * @param int                       $limit
     * @param int                       $ttl
     * @param string                    $configFile
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        ThrottlerFactoryInterface $throttlerFactory,
        HydratorFactory $hydratorFactory,
        $limit,
        $ttl,
        $configFile = null
    ) {
        $this->limit = $limit;
        $this->ttl = $ttl;

        // Default config from distribution
        if (null === $configFile) {
            $configFile = __DIR__.'/../config/config.php';
        }

        $config = include $configFile;

        if ('desarrolla' === $config['adapter']) {
            $cacheFactory = new DesarrollaCacheFactory();
            $cache = $cacheFactory->make($config);

            $this->adapter = new DesarrollaCacheAdapter($cache);
        } else {
            throw new \InvalidArgumentException('No adapter found, please check your config.');
        }

        $this->throttlerFactory = $throttlerFactory;
        $this->hydratorFactory = $hydratorFactory;
    }

    /**
     * Build the throttler for given data
     *
     * @param mixed $data
     * @param int|null $limit
     * @param int|null $ttl
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function get($data, $limit = null, $ttl = null)
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Invalid data, please check the data.');
        }

        $limit = is_null($limit) ? $this->limit : $limit;
        $ttl = is_null($ttl) ? $this->ttl : $ttl;

        $object = $this->hydratorFactory->make($data)->hydrate($data, $limit, $ttl);

        if (!isset($this->throttlers[$object->getKey()])) {
            /** @noinspection PhpParamsInspection */
            $this->throttlers[$object->getKey()] = $this->throttlerFactory->make($object, $this->adapter);
        }

        return $this->throttlers[$object->getKey()];
    }
}
