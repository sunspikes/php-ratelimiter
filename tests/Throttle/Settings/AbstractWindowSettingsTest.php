<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Settings;

use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Throttle\Settings\AbstractWindowSettings;

abstract class AbstractWindowSettingsTest extends TestCase
{
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
