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

final class LeakyBucketThrottler implements ThrottlerInterface
{
    const TIME_CACHE_KEY = ':time';
    const TOKEN_CACHE_KEY = ':tokens';

    /**
     * @var CacheAdapterInterface
     */
    private $cache;

    /**
     * @var int|null
     */
    private $cacheTtl;

    /**
     * @var string
     */
    private $key;

    /**
     * @var int
     */
    private $threshold;

    /**
     * @var TimeAdapterInterface
     */
    private $timeProvider;

    /**
     * @var int
     */
    private $timeLimit;

    /**
     * @var int
     */
    private $tokenlimit;

    /**
     * @param CacheAdapterInterface $cache
     * @param TimeAdapterInterface  $timeAdapter
     * @param string                $key          Cache key prefix
     * @param int                   $tokenLimit   Bucket capacity
     * @param int                   $timeLimit    Refill time
     * @param int|null              $threshold    Capacity threshold on which to start throttling (default: $tokenLimit)
     * @param int|null              $cacheTtl     Cache ttl time (default: cache ttl)
     */
    public function __construct(
        CacheAdapterInterface $cache,
        TimeAdapterInterface $timeAdapter,
        $key,
        $tokenLimit,
        $timeLimit,
        $threshold = null,
        $cacheTtl = null
    ) {
        $this->cache = $cache;
        $this->timeProvider = $timeAdapter;
        $this->key = $key;
        $this->tokenlimit = $tokenLimit;
        $this->timeLimit = $timeLimit;
        $this->cacheTtl = $cacheTtl;
        $this->threshold = null !== $threshold ? $threshold : $tokenLimit;

        // Clear the bucket
        $this->setUsedCapacity(0);
    }

    /**
     * @inheritdoc
     */
    public function access()
    {
        return 0 === $this->hit();
    }

    /**
     * @inheritdoc
     */
    public function hit()
    {
        $tokenCount = $this->count();

        if (0 !== $wait = $this->getWaitTime($tokenCount)) {
            $this->timeProvider->sleep($wait);
        }

        $this->setUsedCapacity($tokenCount + 1);

        return $wait;
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->setUsedCapacity(0);
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

            $lastTokenCount = $this->cache->get($this->key.self::TOKEN_CACHE_KEY);
        } catch (ItemNotFoundException $exception) {
            $this->clear();

            return 0;
        }

        return  (int) ceil($lastTokenCount - ($this->tokenlimit * $timeSinceLastRequest / $this->timeLimit));
    }

    /**
     * @inheritdoc
     */
    public function check()
    {
        return 0 === $this->getWaitTime($this->count());
    }

    /**
     * @inheritdoc
     */
    public function getTtl()
    {
        return $this->timeLimit;
    }

    /**
     * @inheritdoc
     */
    public function getLimit()
    {
        return $this->tokenlimit;
    }

    /**
     * @param int $tokenCount
     *
     * @return int
     */
    private function getWaitTime($tokenCount)
    {
        if ($this->threshold > $tokenCount) {
            return 0;
        }

        return ceil($this->timeLimit/$this->tokenlimit);
    }

    /**
     * @param int $tokens
     */
    private function setUsedCapacity($tokens)
    {
        $this->cache->set($this->key.self::TOKEN_CACHE_KEY, $tokens, $this->cacheTtl);
        $this->cache->set($this->key.self::TIME_CACHE_KEY, $this->timeProvider->now(), $this->cacheTtl);
    }
}
