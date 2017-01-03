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
use Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException;

class ElasticWindowThrottler implements RetriableThrottlerInterface, \Countable
{
    /* @var CacheAdapterInterface */
    protected $cache;
    /* @var string */
    protected $key;
    /* @var int */
    protected $limit;
    /* @var int */
    protected $ttl;
    /* @var int */
    protected $counter;

    /**
     * @param CacheAdapterInterface $cache
     * @param string $key
     * @param int $limit
     * @param int $ttl
     */
    public function __construct(CacheAdapterInterface $cache, $key, $limit, $ttl)
    {
        $this->cache = $cache;
        $this->key = $key;
        $this->limit = $limit;
        $this->ttl = $ttl;
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
    public function hit()
    {
        $this->counter = $this->count() + 1;

        $this->cache->set($this->key, $this->counter, $this->ttl);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->counter = 0;

        $this->cache->set($this->key, $this->counter, $this->ttl);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        if (!is_null($this->counter)) {
            return $this->counter;
        }

        try {
            $this->counter = $this->cache->get($this->key);
        } catch (ItemNotFoundException $e) {
            $this->counter = 0;
        }

        return $this->counter;
    }

    /**
     * @inheritdoc
     */
    public function check()
    {
        return ($this->count() < $this->limit);
    }

    /**
     * @inheritdoc
     */
    public function getTime()
    {
        return $this->ttl;
    }

    /**
     * @inheritdoc
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @inheritdoc
     */
    public function getRetryTimeout()
    {
        if ($this->check()) {
            return 0;
        }

        return self::SECOND_TO_MILLISECOND_MULTIPLIER * $this->ttl;
    }
}
