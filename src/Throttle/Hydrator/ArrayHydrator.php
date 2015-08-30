<?php

namespace Sunspikes\Ratelimit\Throttle\Hydrator;

use Sunspikes\Ratelimit\Throttle\Entity\Data;

class ArrayHydrator implements DataHydratorContract
{
    public function hydrate($data, $limit, $ttl)
    {
        $string = implode('', $data);

        return new Data($string, $limit, $ttl);
    }
}