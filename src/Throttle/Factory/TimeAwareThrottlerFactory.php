<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Krishnaprasad MG <sunspikes@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Sunspikes\Ratelimit\Throttle\Factory;

use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Throttle\Entity\Data;
use Sunspikes\Ratelimit\Throttle\Settings\FixedWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\LeakyBucketSettings;
use Sunspikes\Ratelimit\Throttle\Settings\MovingWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\RetrialQueueSettings;
use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;
use Sunspikes\Ratelimit\Throttle\Throttler\FixedWindowThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\LeakyBucketThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\MovingWindowThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\RetrialQueueThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\ThrottlerInterface;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

class TimeAwareThrottlerFactory extends ThrottlerFactory
{
    /**
     * @var TimeAdapterInterface
     */
    private $timeAdapter;

    /**
     * @param CacheAdapterInterface $cacheAdapter
     * @param TimeAdapterInterface  $timeAdapter
     */
    public function __construct(CacheAdapterInterface $cacheAdapter, TimeAdapterInterface $timeAdapter)
    {
        parent::__construct($cacheAdapter);
        $this->timeAdapter = $timeAdapter;
    }

    /**
     * @inheritdoc
     */
    protected function createThrottler(Data $data, ThrottleSettingsInterface $settings)
    {
        if ($settings instanceof RetrialQueueSettings) {
            return new RetrialQueueThrottler(
                $this->createNestableController($data, $settings->getInternalThrottlerSettings()),
                $this->timeAdapter
            );
        }

        return $this->createNestableController($data, $settings);
    }

    /**
     * @param Data                      $data
     * @param ThrottleSettingsInterface $settings
     *
     * @return ThrottlerInterface
     */
    private function createNestableController(Data $data, ThrottleSettingsInterface $settings)
    {
        if ($settings instanceof LeakyBucketSettings) {
            return new LeakyBucketThrottler(
                $this->cacheAdapter,
                $this->timeAdapter,
                $data->getKey(),
                $settings->getTokenLimit(),
                $settings->getTimeLimit(),
                $settings->getThreshold(),
                $settings->getCacheTtl()
            );
        }

        if ($settings instanceof MovingWindowSettings) {
            return new MovingWindowThrottler(
                $this->cacheAdapter,
                $this->timeAdapter,
                $data->getKey(),
                $settings->getHitLimit(),
                $settings->getTimeLimit(),
                $settings->getCacheTtl()
            );
        }

        if ($settings instanceof FixedWindowSettings) {
            return new FixedWindowThrottler(
                $this->cacheAdapter,
                $this->timeAdapter,
                $data->getKey(),
                $settings->getHitLimit(),
                $settings->getTimeLimit(),
                $settings->getCacheTtl()
            );
        }

        return parent::createThrottler($data, $settings);
    }
}
