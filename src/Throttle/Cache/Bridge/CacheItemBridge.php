<?php

namespace Sunspikes\src\Throttle\Cache\Bridge;

use Cache\Adapter\Common\CacheItem;
use Psr\Cache\CacheItemInterface;

class CacheItemBridge extends CacheItem implements CacheItemInterface
{
    public function __construct(string $key, string $value = null)
    {
        parent::__construct($key, null, $value);
    }
}