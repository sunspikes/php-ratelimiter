<?php

declare(strict_types=1);

namespace Sunspikes\Ratelimit\Throttle\Throttler;

use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException;

class ElasticWindowThrottler implements RetriableThrottlerInterface, \Countable
{
    private ?int $counter = null;

    public function __construct(
        protected CacheAdapterInterface $cache,
        protected string $key,
        protected int $limit,
        protected int $ttl
    ) {
    }

    public function access(): bool
    {
        $status = $this->check();

        $this->hit();

        return $status;
    }

    public function hit(): mixed
    {
        $this->counter = $this->count() + 1;

        $this->cache->set($this->key, $this->counter, $this->ttl);

        return $this;
    }

    public function clear(): void
    {
        $this->counter = 0;

        $this->cache->set($this->key, $this->counter, $this->ttl);
    }

    public function count(): int
    {
        if (null !== $this->counter) {
            return $this->counter;
        }

        try {
            $this->counter = $this->cache->get($this->key);
        } catch (ItemNotFoundException $e) {
            $this->counter = 0;
        }

        return $this->counter;
    }

    public function check(): bool
    {
        return ($this->count() < $this->limit);
    }

    public function getTime(): int
    {
        return $this->ttl;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getRetryTimeout(): int|float
    {
        if ($this->check()) {
            return 0;
        }

        return self::SECOND_TO_MILLISECOND_MULTIPLIER * $this->ttl;
    }
}
