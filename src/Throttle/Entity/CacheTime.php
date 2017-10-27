<?php

namespace Sunspikes\Ratelimit\Throttle\Entity;

use Sunspikes\Ratelimit\Cache\ThrottlerItemInterface;

class CacheTime implements ThrottlerItemInterface
{
    /** @var float $limit */
    private $time;

    /** @var int $ttl */
    private $ttl;

    /**
     * @param float $time
     * @param int $ttl
     */
    public function __construct(float $time, int $ttl = null)
    {
        $this->time = $time;
        $this->ttl = $ttl;
    }

    /**
     * @param array $array
     *
     * @return ThrottlerItemInterface
     */
    public static function createFromArray(array $array): ThrottlerItemInterface
    {
        return new static(
            $array['time'],
            $array['ttl']
        );
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'time' => $this->time,
            'ttl' => $this->ttl,
        ];
    }

    /**
     * @return float
     */
    public function getTime(): float
    {
        return $this->time;
    }

    /**
     * @return float|null
     */
    public function getTtl()
    {
        return $this->ttl;
    }
}