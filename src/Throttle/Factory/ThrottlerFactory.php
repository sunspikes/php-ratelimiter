<?php

namespace Sunspikes\Ratelimit\Throttle\Factory;

use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterContract;
use Sunspikes\Ratelimit\Throttle\Entity\Data;
use Sunspikes\Ratelimit\Throttle\Throttler\CacheThrottler;

class ThrottlerFactory implements FactoryContract
{
    public function make(Data $data, CacheAdapterContract $cache)
    {
        $throttler = new CacheThrottler(
            $cache,
            $data->getKey(),
            $data->getLimit(),
            $data->getTtl()
        );

        return $throttler;
    }
}