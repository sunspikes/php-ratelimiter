<?php
/**
 * The MIT License (MIT).
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
use Sunspikes\Ratelimit\Cache\ThrottlerCacheInterface;
use Sunspikes\Ratelimit\Throttle\Entity\CacheTime;
use Sunspikes\Ratelimit\Throttle\Entity\CacheCount;
use Sunspikes\Ratelimit\Throttle\Settings\FixedWindowSettings;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

final class FixedWindowThrottler extends AbstractWindowThrottler
{
    const CACHE_KEY_TIME = '-time';
    const CACHE_KEY_HITS = '-hits';

    /**
     * @var string
     */
    private $key;

    /**
     * @var int|null
     */
    private $hitCount;

    /**
     * FixedWindowThrottler constructor.
     *
     * @param string                  $key
     * @param ThrottlerCacheInterface $cache
     * @param FixedWindowSettings     $settings
     * @param TimeAdapterInterface    $timeAdapter
     */
    public function __construct(
        string $key,
        ThrottlerCacheInterface $cache,
        FixedWindowSettings $settings,
        TimeAdapterInterface $timeAdapter
    ) {
        parent::__construct($cache, $settings, $timeAdapter);
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function hit(): ThrottlerInterface
    {
        $this->setCachedHitCount($this->count() + 1);
        $item = new CacheTime($this->timeProvider->now(), $this->settings->getCacheTtl());
        // Update the window start time if the previous window has passed, or no cached window exists
        try {
            /** @var CacheTime $currentItem */
            $currentItem = $this->cache->getItem($this->getTimeCacheKey());
            if (($this->timeProvider->now() - $currentItem->getTime()) > $this->settings->getTimeLimit()) {
                $this->cache->setItem($this->getTimeCacheKey(), $item);
            }
        } catch (ItemNotFoundException $exception) {
            $this->cache->setItem($this->getTimeCacheKey(), $item);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        try {
            /** @var CacheTime $currentItem */
            $currentItem = $this->cache->getItem($this->getTimeCacheKey());
            if (($this->timeProvider->now() - $currentItem->getTime()) > $this->settings->getTimeLimit()) {
                return 0;
            }

            return $this->getCachedHitCount();
        } catch (ItemNotFoundException $exception) {
            return 0;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->setCachedHitCount(0);
        $this->cache->setItem(
            $this->getTimeCacheKey(),
            new CacheTime($this->timeProvider->now(), $this->settings->getCacheTtl())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRetryTimeout(): int
    {
        if ($this->check()) {
            return 0;
        }

        // Return the time until the current window ends
        // Try/catch for the ItemNotFoundException is not required, in that case $this->check() will return true
        /** @var CacheTime $cachedTime */
        $cachedTime = $this->cache->getItem($this->getTimeCacheKey());

        return self::SECOND_TO_MILLISECOND_MULTIPLIER * ($this->settings->getTimeLimit() - $this->timeProvider->now(
                ) + $cachedTime->getTime());
    }

    /**
     * @return int
     *
     * @throws ItemNotFoundException
     */
    private function getCachedHitCount(): int
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
        $this->cache->setItem($this->getHitsCacheKey(), new CacheCount($hitCount, $this->settings->getCacheTtl()));
    }

    /**
     * @return string
     */
    private function getHitsCacheKey(): string
    {
        return $this->key.self::CACHE_KEY_HITS;
    }

    /**
     * @return string
     */
    private function getTimeCacheKey(): string
    {
        return $this->key.self::CACHE_KEY_TIME;
    }
}
