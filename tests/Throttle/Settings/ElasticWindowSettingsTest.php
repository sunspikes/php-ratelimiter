<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Settings;

use Mockery as M;
use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Throttle\Settings\ElasticWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;

class ElasticWindowSettingsTest extends TestCase
{
    public function testMergeWithEmpty(): void
    {
        $settings = new ElasticWindowSettings(3, 600);
        $mergedSettings = $settings->merge(new ElasticWindowSettings());

        self::assertEquals(3, $mergedSettings->getLimit());
        self::assertEquals(600, $mergedSettings->getTime());
    }

    public function testMergeWithNonEmpty(): void
    {
        $settings = new ElasticWindowSettings(null, 600);
        $mergedSettings = $settings->merge(new ElasticWindowSettings(3, 700));

        self::assertEquals(3, $mergedSettings->getLimit());
        self::assertEquals(700, $mergedSettings->getTime());
    }

    public function testInvalidMerge(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new ElasticWindowSettings())->merge(M::mock(ThrottleSettingsInterface::class));
    }

    /**
     * @dataProvider inputProvider
     */
    public function testIsValid(?int $limit, ?int $time, bool $result): void
    {
        self::assertEquals($result, (new ElasticWindowSettings($limit, $time))->isValid());
    }

    public static function inputProvider(): array
    {
        return [
            [null, null, false],
            [null, 600, false],
            [3, null, false],
            [3, 600, true],
        ];
    }
}
