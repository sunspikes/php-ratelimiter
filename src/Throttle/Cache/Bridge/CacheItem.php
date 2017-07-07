<?php

namespace Sunspikes\src\Throttle\Cache\Bridge;

use Cache\Adapter\Common\CacheItem as PhpCacheItem;
use Psr\Cache\CacheItemInterface;

class CacheItem extends PhpCacheItem implements CacheItemInterface
{
    public function __construct(string $key, string $value = null)
    {
        parent::__construct($key, null, $value);
    }
}