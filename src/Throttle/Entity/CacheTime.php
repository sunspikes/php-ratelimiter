<?php

namespace Sunspikes\Ratelimit\Throttle\Entity;

use Sunspikes\Ratelimit\Cache\AbstractCacheItem;
use Sunspikes\Ratelimit\Cache\ThrottlerItemInterface;

class CacheTime extends AbstractCacheItem implements ThrottlerItemInterface
{
    /** @var float $limit */
    private $time;

    /** @var int|null $ttl */
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
     * @return float
     */
    public function getTime(): float
    {
        return $this->time;
    }

    /**
     * @inheritdoc
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * @inheritdoc
     */
    protected function fromArray(array $array)
    {
        $this->time = $array['time'];
        $this->ttl = $array['ttl'];
    }

    /**
     * @inheritdoc
     */
    protected function toArray(): array
    {
        return [
            'time' => $this->time,
            'ttl' => $this->ttl,
        ];
    }
}