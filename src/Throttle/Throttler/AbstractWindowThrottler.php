<?php

declare(strict_types=1);

namespace Sunspikes\Ratelimit\Throttle\Throttler;

use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

abstract class AbstractWindowThrottler implements ThrottlerInterface
{
    public function __construct(
        protected CacheAdapterInterface $cache,
        protected TimeAdapterInterface $timeProvider,
        protected string $key,
        protected int $hitLimit,
        protected int $timeLimit,
        protected ?int $cacheTtl = null
    ) {
    }

    public function access(): bool
    {
        $status = $this->check();
        $this->hit();

        return $status;
    }

    public function check(): bool
    {
        return $this->count() < $this->hitLimit;
    }

    public function getTime(): int
    {
        return $this->timeLimit;
    }

    public function getLimit(): int
    {
        return $this->hitLimit;
    }

    abstract public function hit(): mixed;

    abstract public function count(): int;

    abstract public function clear(): void;
}
