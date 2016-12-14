<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Settings;

use Mockery as M;
use Sunspikes\Ratelimit\Throttle\Settings\FixedWindowSettings;

class FixedWindowSettingsTest extends AbstractWindowSettingsTest
{
    /**
     * @inheritdoc
     */
    protected function getSettings($hitLimit = null, $timeLimit = null, $cacheTtl = null)
    {
        return new FixedWindowSettings($hitLimit, $timeLimit, $cacheTtl);
    }
}
