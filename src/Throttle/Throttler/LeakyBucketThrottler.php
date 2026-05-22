<?php

declare(strict_types=1);

namespace Sunspikes\Ratelimit\Throttle\Throttler;

use Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException;
use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

final class LeakyBucketThrottler implements RetriableThrottlerInterface
{
    const CACHE_KEY_TIME = '_time';
    const CACHE_KEY_TOKEN = '_tokens';

    private int $threshold;

    public function __construct(
        private CacheAdapterInterface $cache,
        private TimeAdapterInterface $timeProvider,
        private string $key,
        private int $tokenLimit,
        private int $timeLimit,
        ?int $threshold = null,
        private ?int $cacheTtl = null
    ) {
        $this->threshold = $threshold ?? 0;
    }

    public function access(): bool
    {
        $status = $this->check();
        $this->hit();

        return $status;
    }

    public function hit(): mixed
    {
        $tokenCount = $this->count();

        $this->setUsedCapacity($tokenCount + 1);

        if (0 < $wait = $this->getWaitTime($tokenCount)) {
            $this->timeProvider->usleep(self::MILLISECOND_TO_MICROSECOND_MULTIPLIER * $wait);
        }

        return $wait;
    }

    public function clear(): void
    {
        $this->setUsedCapacity(0);
    }

    public function count(): int
    {
        try {
            $cachedTime = $this->cache->get($this->getTimeCacheKey());
            $timeSinceLastRequest = self::SECOND_TO_MILLISECOND_MULTIPLIER * ($this->timeProvider->now() - $cachedTime);

            if ($timeSinceLastRequest > $this->timeLimit) {
                return 0;
            }

            $lastTokenCount = $this->cache->get($this->getTokenCacheKey());
        } catch (ItemNotFoundException $exception) {
            $this->clear();

            return 0;
        }

        return (int) max(0, ceil($lastTokenCount - ($this->tokenLimit * $timeSinceLastRequest / ($this->timeLimit))));
    }

    public function check(): bool
    {
        return 0 === $this->getWaitTime($this->count());
    }

    public function getTime(): int
    {
        return $this->timeLimit;
    }

    public function getLimit(): int
    {
        return $this->tokenLimit;
    }

    public function getRetryTimeout(): int|float
    {
        if ($this->threshold > $this->count()) {
            return 0;
        }

        return (int) ceil($this->timeLimit / max(1, $this->tokenLimit));
    }

    private function getWaitTime(int $tokenCount): int
    {
        if ($this->threshold > $tokenCount) {
            return 0;
        }

        return (int) ceil($this->timeLimit / max(1, ($this->tokenLimit - $this->threshold)));
    }

    private function setUsedCapacity(int $tokens): void
    {
        $this->cache->set($this->getTokenCacheKey(), $tokens, $this->cacheTtl);
        $this->cache->set($this->getTimeCacheKey(), $this->timeProvider->now(), $this->cacheTtl);
    }

    private function getTokenCacheKey(): string
    {
        return $this->key . self::CACHE_KEY_TOKEN;
    }

    private function getTimeCacheKey(): string
    {
        return $this->key . self::CACHE_KEY_TIME;
    }
}
