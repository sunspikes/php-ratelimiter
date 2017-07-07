<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Settings;

use Mockery as M;
use Sunspikes\Ratelimit\Throttle\Settings\FixedThrottleSettings;

class FixedWindowSettingsTest extends AbstractWindowSettingsTest
{
    /**
     * @inheritdoc
     */
    protected function getSettings($hitLimit = null, $timeLimit = null, $cacheTtl = null)
    {
        return new FixedThrottleSettings($hitLimit, $timeLimit, $cacheTtl);
    }
}
