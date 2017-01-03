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

final class MovingWindowThrottler extends AbstractWindowThrottler implements RetriableThrottlerInterface
{
    /**
     * [Timestamp => recorded hits]
     *
     * @var array
     */
    private $hitCountMapping = [];

    /**
     * @inheritdoc
     */
    public function hit()
    {
        $timestamp = (int) ceil($this->timeProvider->now());
        $this->updateHitCount();

        if (!isset($this->hitCountMapping[$timestamp])) {
            $this->hitCountMapping[$timestamp] = 0;
        }

        //Adds 1 recorded hit to the mapping entry for the current timestamp
        $this->hitCountMapping[$timestamp]++;
        $this->cache->set($this->key, serialize($this->hitCountMapping), $this->cacheTtl);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        $this->updateHitCount();

        return (int) array_sum($this->hitCountMapping);
    }

    /**
     * @inheritdoc
     */
    public function getRetryTimeout()
    {
        if ($this->hitLimit > $totalHitCount = $this->count()) {
            return 0;
        }

        // Check at which 'oldest' possible timestamp enough hits have expired
        // Then return the time remaining for that timestamp to expire
        foreach ($this->hitCountMapping as $timestamp => $hitCount) {
            if ($this->hitLimit > $totalHitCount -= $hitCount) {
                return self::SECOND_TO_MILLISECOND_MULTIPLIER * max(
                    0,
                    $this->timeLimit - ((int) ceil($this->timeProvider->now()) - $timestamp)
                );
            }
        }

        return self::SECOND_TO_MILLISECOND_MULTIPLIER * $this->timeLimit;
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->hitCountMapping = [];
        $this->cache->set($this->key, serialize([]), $this->cacheTtl);
    }

    private function updateHitCount()
    {
        try {
            // Get a stored mapping from cache
            if (0 === count($this->hitCountMapping)) {
                $this->hitCountMapping = (array) unserialize($this->cache->get($this->key));
            }
        } catch (ItemNotFoundException $exception) {}

        $startTime = (int) ceil($this->timeProvider->now()) - $this->timeLimit;

        // Clear all entries older than the window front-edge
        $relevantTimestamps = array_filter(array_keys($this->hitCountMapping), function ($key) use ($startTime) {
            return $startTime <= $key;
        });

        $this->hitCountMapping = array_intersect_key($this->hitCountMapping, array_flip($relevantTimestamps));
    }
}
