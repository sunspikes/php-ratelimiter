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
use Sunspikes\Ratelimit\Cache\ThrottlerItemInterface;
use Sunspikes\Ratelimit\Throttle\Entity\CacheCount;
use Sunspikes\Ratelimit\Throttle\Settings\ElasticWindowSettings;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

final class ElasticWindowThrottler extends AbstractWindowThrottler
{
    /* @var string */
    private $key;

    /* @var int */
    private $counter;

    /** @var int $lastTtl */
    protected $lastTtl;

    /**
     * ElasticWindowThrottler constructor.
     *
     * @param string                  $key
     * @param ThrottlerCacheInterface $cache
     * @param ElasticWindowSettings   $settings
     * @param TimeAdapterInterface    $timeAdapter
     */
    public function __construct(
        string $key,
        ThrottlerCacheInterface $cache,
        ElasticWindowSettings $settings,
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
        $this->counter = $this->count() + 1;
        $this->cache->setItem($this->key, $this->makeCacheCount($this->counter));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->counter = 0;
        $this->cache->setItem($this->key, $this->makeCacheCount($this->counter));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        try {
            /** @var CacheCount $item */
            $item = $this->cache->getItem($this->key);
            $this->counter = $item->getCount();
            $this->lastTtl = $item->getTtl();
        } catch (ItemNotFoundException $e) {
            $this->counter = 0;
            $this->lastTtl = null;
        }

        return $this->counter;
    }

    /**
     * {@inheritdoc}
     */
    public function getRetryTimeout(): int
    {
        if ($this->check()) {
            return 0;
        }

        return self::SECOND_TO_MILLISECOND_MULTIPLIER * $this->settings->getTimeLimit();
    }

    /**
     * @param int $count
     *
     * @return ThrottlerItemInterface
     */
    private function makeCacheCount(int $count): ThrottlerItemInterface
    {
        return new CacheCount(
            $count,
            $this->lastTtl ?? $this->timeProvider->now() + $this->settings->getTimeLimit()
        );
    }
}
