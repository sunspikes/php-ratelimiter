<?php

namespace Sunspikes\Ratelimit\Throttle\Entity;

use Sunspikes\Ratelimit\Cache\ThrottlerItemInterface;
use Sunspikes\Ratelimit\Cache\AbstractCacheItem;

class CacheHitMapping extends AbstractCacheItem implements ThrottlerItemInterface
{
    /** @var array $hitMapping */
    private $hitMapping;

    /** @var int|null $ttl */
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
     * @return array
     */
    public function getHitMapping(): array
    {
        return $this->hitMapping;
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
        $this->hitMapping = $array['hitMapping'];
        $this->ttl = $array['ttl'];
    }

    /**
     * @inheritdoc
     */
    protected function toArray(): array
    {
        return [
            'hitMapping' => $this->hitMapping,
            'ttl' => $this->ttl,
        ];
    }
}