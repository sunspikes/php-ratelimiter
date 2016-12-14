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

use Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException;
use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

final class MovingWindowThrottler implements ThrottlerInterface
{
    const TIME_CACHE_KEY = ':time';
    const HITS_CACHE_KEY = ':hits';

    /**
     * @var CacheAdapterInterface
     */
    private $cache;

    /**
     * @var int|null
     */
    private $cacheTtl;

    /**
     * @var int
     */
    private $hitLimit;

    /**
     * @var string
     */
    private $key;

    /**
     * @var int
     */
    private $timeLimit;

    /**
     * @var TimeAdapterInterface
     */
    private $timeProvider;

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
    public function hit()
    {
        $this->cache->set($this->key.self::HITS_CACHE_KEY, $this->count() + 1, $this->cacheTtl);
        $this->cache->set($this->key.self::TIME_CACHE_KEY, $this->timeProvider->now(), $this->cacheTtl);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->cache->set($this->key.self::HITS_CACHE_KEY, 0, $this->cacheTtl);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        try {
            $timeSinceLastRequest = $this->timeProvider->now() - $this->cache->get($this->key.self::TIME_CACHE_KEY);

            if ($timeSinceLastRequest > $this->timeLimit) {
                return 0;
            }

            return  (int) max(0, ceil(
                $this->cache->get($this->key.self::HITS_CACHE_KEY) -
                ($this->hitLimit * $timeSinceLastRequest / ($this->timeLimit))
            ));
        } catch (ItemNotFoundException $exception) {
            return 0;
        }
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
}
