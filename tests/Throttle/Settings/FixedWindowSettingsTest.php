<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Settings;

use Sunspikes\Ratelimit\Throttle\Settings\FixedWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;

class FixedWindowSettingsTest extends \PHPUnit_Framework_TestCase
{
    public function testMergeWithEmpty()
    {
        $settings = new FixedWindowSettings(3, 600);
        $mergedSettings = $settings->merge(new FixedWindowSettings());

        self::assertEquals(3, $mergedSettings->getLimit());
        self::assertEquals(600, $mergedSettings->getTime());
    }

    public function testMergeWithNonEmpty()
    {
        $settings = new FixedWindowSettings(null, 600);
        $mergedSettings = $settings->merge(new FixedWindowSettings(3, 700));

        self::assertEquals(3, $mergedSettings->getLimit());
        self::assertEquals(700, $mergedSettings->getTime());
    }

    public function testInvalidMerge()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        (new FixedWindowSettings())->merge(\Mockery::mock(ThrottleSettingsInterface::class));
    }

    /**
     * @dataProvider inputProvider
     */
    public function testIsValid($limit, $time, $result)
    {
        self::assertEquals($result, (new FixedWindowSettings($limit, $time))->isValid());
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
            [3, 600, true],
        ];
    }
}
