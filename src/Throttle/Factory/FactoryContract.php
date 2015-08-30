<?php

namespace Sunspikes\Ratelimit\Throttle\Factory;

use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterContract;
use Sunspikes\Ratelimit\Throttle\Entity\Data;

interface FactoryContract
{
    public function make(Data $data, CacheAdapterContract $cache);
}