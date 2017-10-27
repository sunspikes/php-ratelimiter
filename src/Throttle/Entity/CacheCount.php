<?php

namespace Sunspikes\Ratelimit\Throttle\Entity;

use Sunspikes\Ratelimit\Cache\ThrottlerItemInterface;

class CacheCount implements ThrottlerItemInterface
{
    /** @var int $count */
    private $count;

    /** @var int $ttl */
    private $ttl;

    /**
     * @param int $count
     * @param int $ttl
     */
    public function __construct(int $count, int $ttl = null)
    {
        $this->count = $count;
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
            $array['count'],
            $array['ttl']
        );
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'count' => $this->count,
            'ttl' => $this->ttl,
        ];
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @return int|null
     */
    public function getTtl()
    {
        return $this->ttl;
    }
}