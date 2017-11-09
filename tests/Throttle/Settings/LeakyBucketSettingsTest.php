<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Settings;

use Mockery as M;
use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Throttle\Settings\LeakyBucketSettings;
use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;

class LeakyBucketSettingsTest extends TestCase
{
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
