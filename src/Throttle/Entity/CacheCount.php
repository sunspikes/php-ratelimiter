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


namespace Sunspikes\Ratelimit\Throttle\Entity;

use Sunspikes\Ratelimit\Cache\AbstractCacheItem;
use Sunspikes\Ratelimit\Cache\ThrottlerItemInterface;

class CacheCount extends AbstractCacheItem implements ThrottlerItemInterface
{
    /** @var int $count */
    private $count;

    /** @var int|null $ttl */
    private $ttl;

    /**
     * @param int $count
     * @param int $ttl
     */
    public function __construct(int $count, int $ttl = null)
    {
        $this->count = $count;
        $this->ttl = $ttl;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @inheritdoc
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * @inheritdoc
     */
    protected function fromArray(array $array)
    {
        $this->count = $array['count'];
        $this->ttl = $array['ttl'];
    }

    /**
     * @inheritdoc
     */
    protected function toArray(): array
    {
        return [
            'count' => $this->count,
            'ttl' => $this->ttl,
        ];
    }
}