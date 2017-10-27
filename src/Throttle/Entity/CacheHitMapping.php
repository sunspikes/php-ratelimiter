<?php

namespace Sunspikes\Ratelimit\Throttle\Entity;

use Sunspikes\Ratelimit\Cache\ThrottlerItemInterface;

class CacheHitMapping implements ThrottlerItemInterface
{
    /** @var array $hitMapping */
    private $hitMapping;

    /** @var int $ttl */
    private $ttl;

    /**
     * @param array $hitMapping
     * @param int $ttl
     */
    public function __construct(array $hitMapping, int $ttl = null)
    {
        $this->hitMapping = $hitMapping;
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
            $array['hitMapping'],
            $array['ttl']
        );
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'hitMapping' => $this->hitMapping,
            'ttl' => $this->ttl,
        ];
    }

    /**
     * @return array
     */
    public function getHitMapping(): array
    {
        return $this->hitMapping;
    }

    /**
     * @return int|null
     */
    public function getTtl()
    {
        return $this->ttl;
    }
}