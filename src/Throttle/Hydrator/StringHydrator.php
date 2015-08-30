<?php

namespace Sunspikes\Ratelimit\Throttle\Hydrator;

use Sunspikes\Ratelimit\Throttle\Entity\Data;

class StringHydrator implements DataHydratorContract
{
    public function hydrate($data, $limit, $ttl)
    {
        new Data($data, $limit, $ttl);
    }
}