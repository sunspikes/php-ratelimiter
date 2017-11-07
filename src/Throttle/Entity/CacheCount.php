<?php

namespace Sunspikes\Ratelimit\Throttle\Entity;

use Sunspikes\Ratelimit\Cache\AbstractCacheItem;
use Sunspikes\Ratelimit\Cache\ThrottlerItemInterface;

class CacheCount extends AbstractCacheItem implements ThrottlerItemInterface
{
    /** @var int $count */
    private $count;

    /** @var int|null $ttl */
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
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
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
        $this->count = $array['count'];
        $this->ttl = $array['ttl'];
    }

    /**
     * @inheritdoc
     */
    protected function toArray(): array
    {
        return [
            'count' => $this->count,
            'ttl' => $this->ttl,
        ];
    }
}