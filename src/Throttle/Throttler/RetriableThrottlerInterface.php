<?php

namespace Sunspikes\Ratelimit\Throttle\Throttler;

interface RetriableThrottlerInterface extends ThrottlerInterface
{
    /**
     * Return the number of milliseconds to wait before a valid request can be made
     *
     * @return int
     */
    public function getRetryTimeout();
}
