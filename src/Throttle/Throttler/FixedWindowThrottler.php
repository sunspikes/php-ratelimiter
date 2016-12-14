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

final class FixedWindowThrottler extends AbstractWindowThrottler
{
    /**
     * @inheritdoc
     */
    public function hit()
    {
        $this->cache->set($this->key.self::HITS_CACHE_KEY, $this->count() + 1, $this->cacheTtl);

        try {
            $this->cache->get($this->key.self::TIME_CACHE_KEY);
        } catch (ItemNotFoundException $exception) {
            $this->cache->set($this->key.self::TIME_CACHE_KEY, $this->timeProvider->now(), $this->cacheTtl);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        parent::clear();
        $this->cache->set($this->key.self::TIME_CACHE_KEY, $this->timeProvider->now(), $this->cacheTtl);
    }
}
