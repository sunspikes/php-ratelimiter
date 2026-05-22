<?php

declare(strict_types=1);

namespace Sunspikes\Ratelimit\Throttle\Throttler;

use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

final class RetrialQueueThrottler implements ThrottlerInterface
{
    public function __construct(
        private RetriableThrottlerInterface $internalThrottler,
        private TimeAdapterInterface $timeProvider
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
        if (0 !== $waitTime = $this->internalThrottler->getRetryTimeout()) {
            $this->timeProvider->usleep(self::MILLISECOND_TO_MICROSECOND_MULTIPLIER * (int) $waitTime);
        }

        return $this->internalThrottler->hit();
    }

    public function clear(): void
    {
        $this->internalThrottler->clear();
    }

    public function count(): int
    {
        return $this->internalThrottler->count();
    }

    public function check(): bool
    {
        return $this->internalThrottler->check();
    }

    public function getTime(): int
    {
        return $this->internalThrottler->getTime();
    }

    public function getLimit(): int
    {
        return $this->internalThrottler->getLimit();
    }
}
