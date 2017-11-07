<?php

namespace Sunspikes\Ratelimit\Cache;

interface ThrottlerItemInterface extends \Serializable
{
    /**
     * @return int|null
     */
    public function getTtl();
}