<?php

declare(strict_types=1);

namespace Sunspikes\Ratelimit\Throttle\Throttler;

use Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException;

final class FixedWindowThrottler extends AbstractWindowThrottler implements RetriableThrottlerInterface
{
    const CACHE_KEY_TIME = '_time';
    const CACHE_KEY_HITS = '_hits';

    private ?int $hitCount = null;

    public function hit(): mixed
    {
        $this->setCachedHitCount($this->count() + 1);

        try {
            if (($this->timeProvider->now() - $this->cache->get($this->getTimeCacheKey())) > $this->timeLimit) {
                $this->cache->set($this->getTimeCacheKey(), $this->timeProvider->now(), $this->cacheTtl);
            }
        } catch (ItemNotFoundException $exception) {
            $this->cache->set($this->getTimeCacheKey(), $this->timeProvider->now(), $this->cacheTtl);
        }

        return $this;
    }

    public function count(): int
    {
        try {
            if (($this->timeProvider->now() - $this->cache->get($this->getTimeCacheKey())) > $this->timeLimit) {
                return 0;
            }

            return $this->getCachedHitCount();
        } catch (ItemNotFoundException $exception) {
            return 0;
        }
    }

    public function clear(): void
    {
        $this->setCachedHitCount(0);
        $this->cache->set($this->getTimeCacheKey(), $this->timeProvider->now(), $this->cacheTtl);
    }

    public function getRetryTimeout(): int|float
    {
        if ($this->check()) {
            return 0;
        }

        $cachedTime = $this->cache->get($this->getTimeCacheKey());

        return self::SECOND_TO_MILLISECOND_MULTIPLIER * ($this->timeLimit - $this->timeProvider->now() + $cachedTime);
    }

    private function getCachedHitCount(): int
    {
        if (null !== $this->hitCount) {
            return $this->hitCount;
        }

        return $this->cache->get($this->getHitsCacheKey());
    }

    private function setCachedHitCount(int $hitCount): void
    {
        $this->hitCount = $hitCount;
        $this->cache->set($this->getHitsCacheKey(), $hitCount, $this->cacheTtl);
    }

    private function getHitsCacheKey(): string
    {
        return $this->key . self::CACHE_KEY_HITS;
    }

    private function getTimeCacheKey(): string
    {
        return $this->key . self::CACHE_KEY_TIME;
    }
}
