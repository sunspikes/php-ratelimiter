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

interface CacheAdapterInterface
{
    /**
     * Get value from cache
     *
     * @param string $key
     *
     * @return mixed
     * 
     * @throws ItemNotFoundException
     */
    public function get($key);

    /**
     * Set value in cache
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     *
     * @return mixed
     */
    public function set($key, $value, $ttl = null);

    /**
     * Delete value from cache
     *
     * @param string $key
     *
     * @return mixed
     */
    public function delete($key);

    /**
     * Check if keyed value exists in cache
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * Clear cache
     *
     * @return void
     */
    public function clear();
}
