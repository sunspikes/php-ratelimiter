<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Settings;

use Mockery as M;
use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Throttle\Settings\FixedWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\RetrialQueueSettings;
use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;

class RetrialQueueSettingsTest extends TestCase
{
    public function testMerge(): void
    {
        $settings = new RetrialQueueSettings(new FixedWindowSettings(120, 60, 3600));
        $mergedSettings = $settings->merge(new RetrialQueueSettings(new FixedWindowSettings(240)));

        self::assertInstanceOf(RetrialQueueSettings::class, $mergedSettings);
        self::assertEquals(240, $mergedSettings->getInternalThrottlerSettings()->getHitLimit());
        self::assertEquals(60, $mergedSettings->getInternalThrottlerSettings()->getTimeLimit());
        self::assertEquals(3600, $mergedSettings->getInternalThrottlerSettings()->getCacheTtl());
    }

    public function testInvalidMerge(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $settings = new RetrialQueueSettings(new FixedWindowSettings(120, 60));
        $settings->merge(M::mock(ThrottleSettingsInterface::class));
    }

    public function testIsValidWithValidInternalSettings(): void
    {
        $settings = new RetrialQueueSettings(new FixedWindowSettings(120, 60));
        self::assertTrue($settings->isValid());
    }

    public function testIsValidWithInvalidInternalSettings(): void
    {
        $settings = new RetrialQueueSettings(new FixedWindowSettings());
        self::assertFalse($settings->isValid());
    }

    public function testGetInternalThrottlerSettings(): void
    {
        $internal = new FixedWindowSettings(120, 60);
        $settings = new RetrialQueueSettings($internal);

        self::assertSame($internal, $settings->getInternalThrottlerSettings());
    }
}
