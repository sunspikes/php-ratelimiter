<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Settings;

use Mockery as M;
use Sunspikes\Ratelimit\Throttle\Settings\MovingWindowSettings;

class MovingWindowSettingsTest extends AbstractWindowSettingsTest
{
    /**
     * @inheritdoc
     */
    protected function getSettings($hitLimit = null, $timeLimit = null, $cacheTtl = null)
    {
        return new MovingWindowSettings($hitLimit, $timeLimit, $cacheTtl);
    }
}
