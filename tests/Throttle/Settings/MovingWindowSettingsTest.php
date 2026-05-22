<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Settings;

use Sunspikes\Ratelimit\Throttle\Settings\AbstractWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\MovingWindowSettings;

class MovingWindowSettingsTest extends AbstractWindowSettingsTest
{
    protected function getSettings(?int $hitLimit = null, ?int $timeLimit = null, ?int $cacheTtl = null): AbstractWindowSettings
    {
        return new MovingWindowSettings($hitLimit, $timeLimit, $cacheTtl);
    }
}
