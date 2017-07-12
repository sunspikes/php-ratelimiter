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

final class LeakyBucketThrottler extends AbstractWindowThrottler implements ThrottlerInterface
{
    const TIME_CACHE_KEY = ':time';
    const TOKEN_CACHE_KEY = ':tokens';

    /**
     * @inheritdoc
     */
    public function hit(): ThrottlerInterface
    {
        $tokenCount = $this->count();

        $this->setUsedCapacity($tokenCount + 1);

        if (0 < $wait = $this->getWaitTime($tokenCount)) {
            $this->timeProvider->usleep(self::MILLISECOND_TO_MICROSECOND_MULTIPLIER * $wait);
        }

        return $wait;
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        try {
            $cachedTime = $this->cache->get($this->getTimeCacheKey());
            $timeSinceLastRequest = self::SECOND_TO_MILLISECOND_MULTIPLIER * ($this->timeProvider->now() - $cachedTime);

            if ($timeSinceLastRequest > $this->settings->getHitLimit()) {
                return 0;
            }

            $lastTokenCount = $this->cache->get($this->getTokenCacheKey());
        } catch (ItemNotFoundException $exception) {
            $this->clear(); //Clear the bucket

            return 0;
        }

        // Return the `used` token count, minus the amount of tokens which have been `refilled` since the previous request
        return  (int) max(0, ceil($lastTokenCount - ($this->settings->getHitLimit() * $timeSinceLastRequest / ($this->settings->getTimeLimit()))));
    }

    /**
     * @inheritdoc
     */
    public function check(): bool
    {
        return 0 === $this->getWaitTime($this->count());
    }

    /**
     * @inheritdoc
     */
    public function getRetryTimeout()
    {
        if ($this->settings->getThreshold() > $this->count() + 1) {
            return 0;
        }

        return (int) ceil($this->settings->getTimeLimit() / $this->settings->getHitLimit());
    }

    /**
     * @param int $tokenCount
     *
     * @return int
     */
    private function getWaitTime($tokenCount)
    {
        if ($this->settings->getThreshold() > $tokenCount) {
            return 0;
        }

        return (int) ceil($this->settings->getTimeLimit() / max(1, ($this->settings->getHitLimit() - $this->settings->getThreshold())));
    }

    /**
     * @param int $tokens
     */
    private function setUsedCapacity($tokens)
    {
        $this->throttlerCache->setItem($this->getTokenCacheKey(), $tokens);
        $this->throttlerCache->setItem($this->getTimeCacheKey(), $this->timeProvider->now());
    }

    /**
     * @return string
     */
    private function getTokenCacheKey()
    {
        return $this->key.self::CACHE_KEY_TOKEN;
    }

    /**
     * @return string
     */
    private function getTimeCacheKey()
    {
        return $this->key.self::CACHE_KEY_TIME;
    }
}
