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
use Sunspikes\Ratelimit\Throttle\Entity\CacheTime;
use Sunspikes\Ratelimit\Throttle\Entity\CacheCount;

final class FixedWindowThrottler extends AbstractWindowThrottler implements RetriableThrottlerInterface
{
    const CACHE_KEY_TIME = '-time';
    const CACHE_KEY_HITS = '-hits';

    /**
     * @var int|null
     */
    private $hitCount;

    /**
     * @inheritdoc
     */
    public function hit()
    {
        $this->setCachedHitCount($this->count() + 1);
        $item = new CacheTime($this->timeProvider->now(), $this->cacheTtl);
        // Update the window start time if the previous window has passed, or no cached window exists
        try {
            /** @var CacheTime $currentItem */
            $currentItem = $this->cache->getItem($this->getTimeCacheKey());
            if (($this->timeProvider->now() - $currentItem->getTime()) > $this->timeLimit) {
                $this->cache->setItem($this->getTimeCacheKey(), $item);
            }
        } catch (ItemNotFoundException $exception) {
            $this->cache->setItem($this->getTimeCacheKey(), $item);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        try {
            /** @var CacheTime $currentItem */
            $currentItem = $this->cache->getItem($this->getTimeCacheKey());
            if (($this->timeProvider->now() - $currentItem->getTime()) > $this->timeLimit) {
                return 0;
            }

            return $this->getCachedHitCount();
        } catch (ItemNotFoundException $exception) {
            return 0;
        }
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->setCachedHitCount(0);
        $item = new CacheTime($this->timeProvider->now(), $this->cacheTtl);
        $this->cache->setItem($this->getTimeCacheKey(), $item);
    }

    /**
     * @inheritdoc
     */
    public function getRetryTimeout()
    {
        if ($this->check()) {
            return 0;
        }

        // Return the time until the current window ends
        // Try/catch for the ItemNotFoundException is not required, in that case $this->check() will return true
        /** @var CacheTime $cachedTime */
        $cachedTime = $this->cache->getItem($this->getTimeCacheKey());

        return self::SECOND_TO_MILLISECOND_MULTIPLIER * ($this->timeLimit - $this->timeProvider->now() + $cachedTime->getTime());
    }

    /**
     * @return int
     *
     * @throws ItemNotFoundException
     */
    private function getCachedHitCount()
    {
        if (null !== $this->hitCount) {
            return $this->hitCount;
        }
        /** @var CacheCount $item */
        $item = $this->cache->getItem($this->getHitsCacheKey());

        return $item->getCount();
    }

    /**
     * @param int $hitCount
     */
    private function setCachedHitCount($hitCount)
    {
        $this->hitCount = $hitCount;
        $item = new CacheCount($hitCount, $this->cacheTtl);
        $this->cache->setItem($this->getHitsCacheKey(), $item);
    }

    /**
     * @return string
     */
    private function getHitsCacheKey()
    {
        return $this->key . self::CACHE_KEY_HITS;
    }

    /**
     * @return string
     */
    private function getTimeCacheKey()
    {
        return $this->key . self::CACHE_KEY_TIME;
    }
}
