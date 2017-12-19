<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Settings;

use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Throttle\Settings\ElasticWindowSettings;

class ElasticWindowSettingsTest extends TestCase
{
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
