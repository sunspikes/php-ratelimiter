<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Settings;

use Mockery as M;
use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Throttle\Settings\AbstractWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;

abstract class AbstractWindowSettingsTest extends TestCase
{
    public function testMergeWithEmpty(): void
    {
        $mergedSettings = $this->getSettings(120, 60, 3600)->merge($this->getSettings());

        self::assertEquals(120, $mergedSettings->getHitLimit());
        self::assertEquals(60, $mergedSettings->getTimeLimit());
        self::assertEquals(3600, $mergedSettings->getCacheTtl());
    }

    public function testMergeWithNonEmpty(): void
    {
        $mergedSettings = $this->getSettings(null, 60, null)->merge($this->getSettings(120, null, null));

        self::assertEquals(120, $mergedSettings->getHitLimit());
        self::assertEquals(60, $mergedSettings->getTimeLimit());
        self::assertEquals(null, $mergedSettings->getCacheTtl());
    }

    public function testInvalidMerge(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->getSettings()->merge(M::mock(ThrottleSettingsInterface::class));
    }

    /**
     * @dataProvider inputProvider
     */
    public function testIsValid(?int $tokenLimit, ?int $timeLimit, bool $result): void
    {
        self::assertEquals($result, $this->getSettings($tokenLimit, $timeLimit)->isValid());
    }

    public static function inputProvider(): array
    {
        return [
            [null, null, false],
            [null, 600, false],
            [3, null, false],
            [3, 0, false],
            [30, 600, true],
        ];
    }

    abstract protected function getSettings(?int $hitLimit = null, ?int $timeLimit = null, ?int $cacheTtl = null): AbstractWindowSettings;
}
