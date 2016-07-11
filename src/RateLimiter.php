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

use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Throttle\Throttler\ThrottlerInterface;
use Sunspikes\Ratelimit\Throttle\Factory\FactoryInterface as ThrottlerFactoryInterface;
use Sunspikes\Ratelimit\Throttle\Hydrator\FactoryInterface as HydratorFactoryInterface;

class RateLimiter
{
    /**
     * @var CacheAdapterInterface
     */
    protected $adapter;

    /**
     * @var ThrottlerInterface[]
     */
    protected $throttlers;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $ttl;

    /**
     * @var ThrottlerFactoryInterface
     */
    protected $throttlerFactory;

    /**
     * @var HydratorFactoryInterface
     */
    protected $hydratorFactory;

    /**
     * @param ThrottlerFactoryInterface $throttlerFactory
     * @param HydratorFactoryInterface  $hydratorFactory
     * @param CacheAdapterInterface     $cacheAdapter
     * @param int                       $limit
     * @param int                       $ttl
     */
    public function __construct(
        ThrottlerFactoryInterface $throttlerFactory,
        HydratorFactoryInterface $hydratorFactory,
        CacheAdapterInterface $cacheAdapter,
        $limit,
        $ttl
    ) {
        $this->throttlerFactory = $throttlerFactory;
        $this->hydratorFactory = $hydratorFactory;
        $this->adapter = $cacheAdapter;
        $this->limit = $limit;
        $this->ttl = $ttl;
    }

    /**
     * Build the throttler for given data
     *
     * @param mixed    $data
     * @param int|null $limit
     * @param int|null $ttl
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function get($data, $limit = null, $ttl = null)
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Invalid data, please check the data.');
        }

        $limit = null === $limit ? $this->limit : $limit;
        $ttl = null === $ttl ? $this->ttl : $ttl;

        // Create the data object
        $dataObject = $this->hydratorFactory->make($data)->hydrate($data, $limit, $ttl);

        if (!isset($this->throttlers[$dataObject->getKey()])) {
            $this->throttlers[$dataObject->getKey()] = $this->throttlerFactory->make($dataObject, $this->adapter);
        }

        return $this->throttlers[$dataObject->getKey()];
    }
}
