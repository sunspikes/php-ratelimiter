<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Settings;

use Mockery as M;
use Sunspikes\Ratelimit\Throttle\Settings\MovingWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;

class MovingWindowSettingsTest extends \PHPUnit_Framework_TestCase
{
    public function testMergeWithEmpty()
    {
        $settings = new MovingWindowSettings(120, 60, 3600);
        $mergedSettings = $settings->merge(new MovingWindowSettings());

        self::assertEquals(120, $mergedSettings->getTokenLimit());
        self::assertEquals(60, $mergedSettings->getTimeLimit());
        self::assertEquals(3600, $mergedSettings->getCacheTtl());
    }

    public function testMergeWithNonEmpty()
    {
        $settings = new MovingWindowSettings(null, 60, null);
        $mergedSettings = $settings->merge(new MovingWindowSettings(120, null, null));

        self::assertEquals(120, $mergedSettings->getTokenLimit());
        self::assertEquals(60, $mergedSettings->getTimeLimit());
        self::assertEquals(null, $mergedSettings->getCacheTtl());
    }

    public function testInvalidMerge()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        (new MovingWindowSettings())->merge(M::mock(ThrottleSettingsInterface::class));
    }

    /**
     * @dataProvider inputProvider
     */
    public function testIsValid($tokenLimit, $timeLimit, $result)
    {
        self::assertEquals($result, (new MovingWindowSettings($tokenLimit, $timeLimit))->isValid());
    }

    /**
     * @return array
     */
    public function inputProvider()
    {
        return [
            [null, null, false],
            [null, 600, false],
            [3, null, false],
            [3, 0, false],
            [30, 600, true],
        ];
    }
}
