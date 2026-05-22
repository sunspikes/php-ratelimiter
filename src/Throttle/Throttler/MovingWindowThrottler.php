<?php

declare(strict_types=1);

namespace Sunspikes\Ratelimit\Throttle\Throttler;

use Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException;

final class MovingWindowThrottler extends AbstractWindowThrottler implements RetriableThrottlerInterface
{
    /** @var array<int, int> */
    private array $hitCountMapping = [];

    public function hit(): mixed
    {
        $timestamp = (int) ceil($this->timeProvider->now());
        $this->updateHitCount();

        if (!isset($this->hitCountMapping[$timestamp])) {
            $this->hitCountMapping[$timestamp] = 0;
        }

        $this->hitCountMapping[$timestamp]++;
        $this->cache->set($this->key, serialize($this->hitCountMapping), $this->cacheTtl);

        return $this;
    }

    public function count(): int
    {
        $this->updateHitCount();

        return (int) array_sum($this->hitCountMapping);
    }

    public function getRetryTimeout(): int|float
    {
        if ($this->hitLimit > $totalHitCount = $this->count()) {
            return 0;
        }

        foreach ($this->hitCountMapping as $timestamp => $hitCount) {
            if ($this->hitLimit > $totalHitCount -= $hitCount) {
                return self::SECOND_TO_MILLISECOND_MULTIPLIER * max(
                    0,
                    $this->timeLimit - ((int) ceil($this->timeProvider->now()) - $timestamp)
                );
            }
        }

        return self::SECOND_TO_MILLISECOND_MULTIPLIER * $this->timeLimit;
    }

    public function clear(): void
    {
        $this->hitCountMapping = [];
        $this->cache->set($this->key, serialize([]), $this->cacheTtl);
    }

    private function updateHitCount(): void
    {
        try {
            if (0 === count($this->hitCountMapping)) {
                $this->hitCountMapping = (array) unserialize($this->cache->get($this->key));
            }
        } catch (ItemNotFoundException $exception) {
        }

        $startTime = (int) ceil($this->timeProvider->now()) - $this->timeLimit;

        $relevantTimestamps = array_filter(array_keys($this->hitCountMapping), function ($key) use ($startTime) {
            return $startTime <= $key;
        });

        $this->hitCountMapping = array_intersect_key($this->hitCountMapping, array_flip($relevantTimestamps));
    }
}
