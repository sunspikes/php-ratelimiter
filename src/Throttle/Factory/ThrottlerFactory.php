<?php
/**
 * The MIT License (MIT).
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

use Sunspikes\Ratelimit\Cache\ThrottlerCacheInterface;
use Sunspikes\Ratelimit\Throttle\Entity\Data;
use Sunspikes\Ratelimit\Throttle\Exception\InvalidThrottlerSettingsException;
use Sunspikes\Ratelimit\Throttle\Settings\ElasticWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\FixedWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\LeakyBucketSettings;
use Sunspikes\Ratelimit\Throttle\Settings\MovingWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\RetrialQueueSettings;
use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;
use Sunspikes\Ratelimit\Throttle\Throttler\ElasticWindowThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\FixedWindowThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\LeakyBucketThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\MovingWindowThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\RetrialQueueThrottler;
use Sunspikes\Ratelimit\Throttle\Throttler\ThrottlerInterface;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

class ThrottlerFactory implements FactoryInterface
{
    /**
     * @var ThrottlerCacheInterface
     */
    protected $throttlerCache;

    /**
     * @var TimeAdapterInterface
     */
    private $timeAdapter;

    /**
     * @param ThrottlerCacheInterface $throttlerCache
     * @param TimeAdapterInterface    $timeAdapter
     */
    public function __construct(ThrottlerCacheInterface $throttlerCache, TimeAdapterInterface $timeAdapter)
    {
        $this->throttlerCache = $throttlerCache;
        $this->timeAdapter = $timeAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function make(Data $data, ThrottleSettingsInterface $settings): ThrottlerInterface
    {
        if (!$settings->isValid()) {
            throw new InvalidThrottlerSettingsException('Provided throttler settings not valid');
        }

        return $this->createThrottler($data, $settings);
    }

    /**
     * {@inheritdoc}
     */
    protected function createThrottler(Data $data, ThrottleSettingsInterface $settings): ThrottlerInterface
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
     *
     * @throws \Sunspikes\Ratelimit\Throttle\Exception\InvalidThrottlerSettingsException
     */
    private function createNestableController(Data $data, ThrottleSettingsInterface $settings): ThrottlerInterface
    {
        if ($settings instanceof LeakyBucketSettings) {
            return new LeakyBucketThrottler(
                $data->getKey(),
                $this->throttlerCache,
                $settings,
                $this->timeAdapter
            );
        }

        if ($settings instanceof MovingWindowSettings) {
            return new MovingWindowThrottler(
                $data->getKey(),
                $this->throttlerCache,
                $settings,
                $this->timeAdapter
            );
        }

        if ($settings instanceof FixedWindowSettings) {
            return new FixedWindowThrottler(
                $data->getKey(),
                $this->throttlerCache,
                $settings,
                $this->timeAdapter
            );
        }

        if ($settings instanceof ElasticWindowSettings) {
            return new ElasticWindowThrottler(
                $data->getKey(),
                $this->throttlerCache,
                $settings,
                $this->timeAdapter
            );
        }

        throw new InvalidThrottlerSettingsException(
            sprintf('Unable to create throttler for %s settings', get_class($settings))
        );
    }
}
