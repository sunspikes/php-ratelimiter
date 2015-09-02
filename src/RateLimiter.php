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

namespace Sunspikes;

use Sunspikes\Ratelimit\Cache\Adapter\DesarrollaCacheAdapter;
use Sunspikes\Ratelimit\Cache\Factory\DesarrollaCacheFactory;
use Sunspikes\Ratelimit\Throttle\Factory\ThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Hydrator\ArrayHydrator;
use Sunspikes\Ratelimit\Throttle\Hydrator\StringHydrator;

class RateLimiter
{
    /* @var \Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterContract */
    private $adapter;
    /* @var array */
    private $throttlers;
    /* @var int */
    private $limit;
    /* @var int */
    private $ttl;

    /**
     * @param array $config
     * @param int $limit
     * @param int $ttl
     * @throws \InvalidArgumentException
     */
    public function __construct(array $config, $limit, $ttl)
    {
        $this->limit = $limit;
        $this->ttl = $ttl;

        if ('desarrolla' == $config['adapter'])
        {
            $cacheFactory = new DesarrollaCacheFactory();
            $cache = $cacheFactory->make($config);

            $this->adapter = new DesarrollaCacheAdapter($cache);
        }

        throw new \InvalidArgumentException('No adapter found, please check your config.');
    }

    /**
     * Build the throttler for given data
     *
     * @param mixed $data
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function get($data)
    {
        if (! empty($data))
        {
            if (is_array($data))
            {
                $data = new ArrayHydrator($data, $this->limit, $this->ttl);
            }
            elseif (is_string($data))
            {
                $data = new StringHydrator($data, $this->limit, $this->ttl);
            }
            else
            {
                throw new \InvalidArgumentException("Unsupported data, please check the data.");
            }

            if (! isset($this->throttlers[$data->getKey()]))
            {
                $factory = new ThrottlerFactory();
                /** @noinspection PhpParamsInspection */
                $this->throttlers[$data->getKey()] = $factory->make($data, $this->adapter);
            }
        }

        return $this->throttlers[$data->getKey()];
    }
}