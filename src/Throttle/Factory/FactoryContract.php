<?php

namespace Sunspikes\Ratelimit\Throttle\Factory;

interface FactoryContract
{
    public function make($data, $limit, $ttl);
}