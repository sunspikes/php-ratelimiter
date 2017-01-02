<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Settings;

use Mockery as M;
use Sunspikes\Ratelimit\Throttle\Settings\ElasticWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;

class ElasticWindowSettingsTest extends \PHPUnit_Framework_TestCase
{
    public function testMergeWithEmpty()
    {
        $settings = new ElasticWindowSettings(3, 600);
        $mergedSettings = $settings->merge(new ElasticWindowSettings());

        self::assertEquals(3, $mergedSettings->getLimit());
        self::assertEquals(600, $mergedSettings->getTime());
    }

    public function testMergeWithNonEmpty()
    {
        $settings = new ElasticWindowSettings(null, 600);
        $mergedSettings = $settings->merge(new ElasticWindowSettings(3, 700));

        self::assertEquals(3, $mergedSettings->getLimit());
        self::assertEquals(700, $mergedSettings->getTime());
    }

    public function testInvalidMerge()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        (new ElasticWindowSettings())->merge(M::mock(ThrottleSettingsInterface::class));
    }

    /**
     * @dataProvider inputProvider
     */
    public function testIsValid($limit, $time, $result)
    {
        self::assertEquals($result, (new ElasticWindowSettings($limit, $time))->isValid());
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
