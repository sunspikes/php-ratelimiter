<?php

namespace Sunspikes\Ratelimit\Throttle\Hydrator;

interface DataHydratorContract
{
    public function hydrate($data, $limit, $ttl);
}