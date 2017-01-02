<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Settings;

use Mockery as M;
use Sunspikes\Ratelimit\Throttle\Settings\LeakyBucketSettings;
use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;

class LeakyBucketSettingsTest extends \PHPUnit_Framework_TestCase
{
    public function testMergeWithEmpty()
    {
        $settings = new LeakyBucketSettings(120, 60, 30, 3600);
        $mergedSettings = $settings->merge(new LeakyBucketSettings());

        self::assertEquals(120, $mergedSettings->getTokenLimit());
        self::assertEquals(60, $mergedSettings->getTimeLimit());
        self::assertEquals(30, $mergedSettings->getThreshold());
        self::assertEquals(3600, $mergedSettings->getCacheTtl());
    }

    public function testMergeWithNonEmpty()
    {
        $settings = new LeakyBucketSettings(null, 60, 30, null);
        $mergedSettings = $settings->merge(new LeakyBucketSettings(120, null, 40, null));

        self::assertEquals(120, $mergedSettings->getTokenLimit());
        self::assertEquals(60, $mergedSettings->getTimeLimit());
        self::assertEquals(40, $mergedSettings->getThreshold());
        self::assertEquals(null, $mergedSettings->getCacheTtl());
    }

    public function testInvalidMerge()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        (new LeakyBucketSettings())->merge(M::mock(ThrottleSettingsInterface::class));
    }

    /**
     * @dataProvider inputProvider
     */
    public function testIsValid($tokenLimit, $timeLimit, $threshold, $result)
    {
        self::assertEquals($result, (new LeakyBucketSettings($tokenLimit, $timeLimit, $threshold))->isValid());
    }

    /**
     * @return array
     */
    public function inputProvider()
    {
        return [
            [null, null, null, false],
            [null, 600, null, false],
            [3, null, null, false],
            [3, 0, null, false],
            [3, 600, 3, true],
            [30, 600, 15, true],
        ];
    }
}
