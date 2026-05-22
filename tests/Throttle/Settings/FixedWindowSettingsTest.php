<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Settings;

use Sunspikes\Ratelimit\Throttle\Settings\AbstractWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\FixedWindowSettings;

class FixedWindowSettingsTest extends AbstractWindowSettingsTest
{
    protected function getSettings(?int $hitLimit = null, ?int $timeLimit = null, ?int $cacheTtl = null): AbstractWindowSettings
    {
        return new FixedWindowSettings($hitLimit, $timeLimit, $cacheTtl);
    }
}
