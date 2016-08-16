<?php

namespace Sunspikes\Ratelimit\Throttle\Settings;

interface ThrottleSettingsInterface
{
    /**
     * @param ThrottleSettingsInterface $settings
     *
     * @return ThrottleSettingsInterface
     */
    public function merge(ThrottleSettingsInterface $settings);

    /**
     * @return bool
     */
    public function isValid();
}
