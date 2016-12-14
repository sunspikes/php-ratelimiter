<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Settings;

use Mockery as M;
use Sunspikes\Ratelimit\Throttle\Settings\AbstractWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;

abstract class AbstractWindowSettingsTest extends \PHPUnit_Framework_TestCase
{
    public function testMergeWithEmpty()
    {
        $mergedSettings = $this->getSettings(120, 60, 3600)->merge($this->getSettings());

        self::assertEquals(120, $mergedSettings->getHitLimit());
        self::assertEquals(60, $mergedSettings->getTimeLimit());
        self::assertEquals(3600, $mergedSettings->getCacheTtl());
    }

    public function testMergeWithNonEmpty()
    {
        $mergedSettings = $this->getSettings(null, 60, null)->merge($this->getSettings(120, null, null));

        self::assertEquals(120, $mergedSettings->getHitLimit());
        self::assertEquals(60, $mergedSettings->getTimeLimit());
        self::assertEquals(null, $mergedSettings->getCacheTtl());
    }

    public function testInvalidMerge()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $this->getSettings()->merge(M::mock(ThrottleSettingsInterface::class));
    }

    /**
     * @dataProvider inputProvider
     */
    public function testIsValid($tokenLimit, $timeLimit, $result)
    {
        self::assertEquals($result, $this->getSettings($tokenLimit, $timeLimit)->isValid());
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

    /**
     * @param int|null $hitLimit
     * @param int|null $timeLimit
     * @param int|null $cacheTtl
     *
     * @return AbstractWindowSettings
     */
    abstract protected function getSettings($hitLimit = null, $timeLimit = null, $cacheTtl = null);
}
