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

namespace Sunspikes\Ratelimit\Cache\Adapter;

use Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException;

/**
 * Adapter for the cache library Desarrolla2\Cache
 */
class DesarrollaCacheAdapter implements CacheAdapterInterface
{
    /* @var \Desarrolla2\Cache\CacheInterface $cache */
    protected $cache;

    /**
     * @param \Desarrolla2\Cache\CacheInterface $cache
     */
    public function __construct($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        throw new ItemNotFoundException('Cannot find the item in cache');
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $ttl = null)
    {
        $this->cache->set($key, $value, $ttl);
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        $this->cache->delete($key);
    }

    /**
     * @inheritdoc
     */
    public function has($key)
    {
        return $this->cache->has($key);
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->cache->clearCache();
    }
}
