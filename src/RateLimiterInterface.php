<?php

namespace Sunspikes\Ratelimit;

use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;
use Sunspikes\Ratelimit\Throttle\Throttler\ThrottlerInterface;

interface RateLimiterInterface
{
    /**
     * Return a throttler for given data and settings
     *
     * @param mixed                          $data
     * @param ThrottleSettingsInterface|null $throttlerSettings
     *
     * @return ThrottlerInterface
     *
     * @throws \InvalidArgumentException
     */
    public function get($data, ThrottleSettingsInterface $throttlerSettings = null);
}
