<?php

namespace Sunspikes\Ratelimit\Throttle\Hydrator;

interface HydratorContract
{
    public function hydrate($data, $limit, $ttl);
}