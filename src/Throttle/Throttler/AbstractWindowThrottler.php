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

namespace Sunspikes\Ratelimit\Throttle\Throttler;

use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

abstract class AbstractWindowThrottler
{
    /**
     * @var CacheAdapterInterface
     */
    protected $cache;

    /**
     * @var int|null
     */
    protected $cacheTtl;

    /**
     * @var int
     */
    protected $hitLimit;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var int
     */
    protected $timeLimit;

    /**
     * @var TimeAdapterInterface
     */
    protected $timeProvider;

    /**
     * @param CacheAdapterInterface $cache
     * @param TimeAdapterInterface  $timeAdapter
     * @param string                $key         Cache key prefix
     * @param int                   $hitLimit    Maximum number of hits
     * @param int                   $timeLimit   Length of window
     * @param int|null              $cacheTtl    Cache ttl time (default: null => CacheAdapter ttl)
     */
    public function __construct(
        CacheAdapterInterface $cache,
        TimeAdapterInterface $timeAdapter,
        $key,
        $hitLimit,
        $timeLimit,
        $cacheTtl = null
    ) {
        $this->cache = $cache;
        $this->timeProvider = $timeAdapter;
        $this->key = $key;
        $this->hitLimit = $hitLimit;
        $this->timeLimit = $timeLimit;
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * @inheritdoc
     */
    public function access()
    {
        $status = $this->check();
        $this->hit();

        return $status;
    }


    /**
     * @inheritdoc
     */
    public function check()
    {
        return $this->count() < $this->hitLimit;
    }

    /**
     * @inheritdoc
     */
    public function getTime()
    {
        return $this->timeLimit;
    }

    /**
     * @inheritdoc
     */
    public function getLimit()
    {
        return $this->hitLimit;
    }

    /**
     * @inheritdoc
     */
    abstract public function hit();

    /**
     * @inheritdoc
     */
    abstract public function count();
}
