<?php

namespace Sunspikes\Ratelimit\Cache;

interface ThrottlerItemInterface extends \JsonSerializable
{
    /**
     * @param array $array
     *
     * @return ThrottlerItemInterface
     */
    public static function createFromArray(array $array): ThrottlerItemInterface;

    /**
     * @return int|null
     */
    public function getTtl();
}