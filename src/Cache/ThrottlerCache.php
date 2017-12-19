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

namespace Sunspikes\Ratelimit\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheException as PsrCacheException;
use Sunspikes\Ratelimit\Cache\Exception\CacheAdapterException;
use Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException;

class ThrottlerCache implements ThrottlerCacheInterface
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;

    /**
     * ThrottlerCache constructor.
     *
     * @param CacheItemPoolInterface $cacheItemPool
     */
    public function __construct(CacheItemPoolInterface $cacheItemPool)
    {
        $this->cacheItemPool = $cacheItemPool;
    }

    /**
     * @inheritdoc
     */
    public function getItem(string $key): ThrottlerItemInterface
    {
        try {
            $item = $this->cacheItemPool->getItem($key);

            if ($item->isHit()) {
                $throttlerItem = unserialize($item->get(), ['allowed_classes' => true]);

                if ($throttlerItem instanceof ThrottlerItemInterface) {
                    return $throttlerItem;
                }
            }
        } catch (PsrCacheException $e) {
            throw new CacheAdapterException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        throw new ItemNotFoundException('Item not found.');
    }

    /**
     * @inheritdoc
     */
    public function hasItem(string $key): bool
    {
        try {
            return $this->cacheItemPool->hasItem($key);
        } catch (PsrCacheException $e) {
            throw new CacheAdapterException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @inheritdoc
     */
    public function setItem(string $key, ThrottlerItemInterface $item): bool
    {
        try {
            $cacheItem = $this->cacheItemPool->getItem($key);
        } catch (PsrCacheException $e) {
            throw new CacheAdapterException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        $cacheItem->set(serialize($item));
        $cacheItem->expiresAt($item->getTtl());

        return $this->cacheItemPool->save($cacheItem);
    }

    /**
     * @inheritdoc
     */
    public function removeItem(string $key)
    {
        try {
            $this->cacheItemPool->deleteItem($key);
        } catch (PsrCacheException $e) {
            throw new CacheAdapterException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }
}