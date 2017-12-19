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
use Sunspikes\Ratelimit\Throttle\Entity\CacheCount;
use Sunspikes\Ratelimit\Throttle\Entity\CacheTime;
use Sunspikes\Ratelimit\Throttle\Settings\LeakyBucketSettings;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

final class LeakyBucketThrottler implements RetriableThrottlerInterface, \Countable
{
    const CACHE_KEY_TIME = '-time';
    const CACHE_KEY_TOKEN = '-tokens';

    /**
     * @var string
     */
    private $key;

    /**
     * @var ThrottlerCacheInterface
     */
    private $cache;

    /**
     * @var LeakyBucketSettings
     */
    private $settings;

    /**
     * @var TimeAdapterInterface
     */
    private $timeProvider;

    /**
     * LeakyBucketThrottler constructor.
     *
     * @param string                  $key
     * @param ThrottlerCacheInterface $cache
     * @param LeakyBucketSettings     $settings
     * @param TimeAdapterInterface    $timeAdapter
     */
    public function __construct(
        string $key,
        ThrottlerCacheInterface $cache,
        LeakyBucketSettings $settings,
        TimeAdapterInterface $timeAdapter
    ) {
        $this->key = $key;
        $this->cache = $cache;
        $this->settings = $settings;
        $this->timeProvider = $timeAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function access(): bool
    {
        return 0 === $this->hit();
    }

    /**
     * {@inheritdoc}
     */
    public function hit(): bool
    {
        $tokenCount = $this->count();

        $this->setUsedCapacity($tokenCount + 1);

        if (0 < $wait = $this->getWaitTime($tokenCount)) {
            $this->timeProvider->usleep(self::MILLISECOND_TO_MICROSECOND_MULTIPLIER * $wait);
        }

        return $wait;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->setUsedCapacity(0);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        try {
            /** @var CacheTime $timeItem */
            $timeItem = $this->cache->getItem($this->getTimeCacheKey());
            $timeSinceLastRequest = self::SECOND_TO_MILLISECOND_MULTIPLIER * (
                    $this->timeProvider->now() - $timeItem->getTime()
                );

            if ($timeSinceLastRequest > $this->settings->getTimeLimit()) {
                return 0;
            }

            /** @var CacheCount $countItem */
            $countItem = $this->cache->getItem($this->getTokenCacheKey());
        } catch (ItemNotFoundException $exception) {
            $this->clear(); //Clear the bucket

            return 0;
        }

        // Return the `used` token count, minus the amount of tokens
        // which have been `refilled` since the previous request
        return (int) max(
            0,
            ceil(
                $countItem->getCount() - (
                    $this->settings->getTokenLimit() * $timeSinceLastRequest / $this->settings->getTimeLimit()
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function check(): bool
    {
        return 0 === $this->getWaitTime($this->count());
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeLimit(): int
    {
        return $this->settings->getTimeLimit();
    }

    /**
     * {@inheritdoc}
     */
    public function getHitLimit(): int
    {
        return $this->settings->getTokenLimit();
    }

    /**
     * {@inheritdoc}
     */
    public function getRetryTimeout(): int
    {
        if ($this->settings->getThreshold() > $this->count() + 1) {
            return 0;
        }

        return (int) ceil($this->settings->getTimeLimit() / $this->settings->getTokenLimit());
    }

    /**
     * @param int $tokenCount
     *
     * @return int
     */
    private function getWaitTime($tokenCount): int
    {
        if ($this->settings->getThreshold() > $tokenCount) {
            return 0;
        }

        return (int) ceil(
            $this->settings->getTimeLimit() / max(
                1,
                $this->settings->getTokenLimit() - $this->settings->getThreshold()
            )
        );
    }

    /**
     * @param int $tokens
     */
    private function setUsedCapacity($tokens)
    {
        $this->cache->setItem(
            $this->getTokenCacheKey(),
            new CacheCount($tokens, $this->settings->getCacheTtl())
        );
        $this->cache->setItem(
            $this->getTimeCacheKey(),
            new CacheTime($this->timeProvider->now(), $this->settings->getCacheTtl())
        );
    }

    /**
     * @return string
     */
    private function getTokenCacheKey(): string
    {
        return $this->key.self::CACHE_KEY_TOKEN;
    }

    /**
     * @return string
     */
    private function getTimeCacheKey(): string
    {
        return $this->key.self::CACHE_KEY_TIME;
    }
}
