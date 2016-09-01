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

namespace Sunspikes\Ratelimit;

use Sunspikes\Ratelimit\Throttle\Entity\Data;
use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;
use Sunspikes\Ratelimit\Throttle\Throttler\ThrottlerInterface;
use Sunspikes\Ratelimit\Throttle\Factory\FactoryInterface as ThrottlerFactoryInterface;
use Sunspikes\Ratelimit\Throttle\Hydrator\FactoryInterface as HydratorFactoryInterface;

class RateLimiter implements RateLimiterInterface
{
    /**
     * @var ThrottlerInterface[]
     */
    protected $throttlers;

    /**
     * @var ThrottlerFactoryInterface
     */
    protected $throttlerFactory;

    /**
     * @var HydratorFactoryInterface
     */
    protected $hydratorFactory;

    /**
     * @var ThrottleSettingsInterface
     */
    private $defaultSettings;

    /**
     * @param ThrottlerFactoryInterface $throttlerFactory
     * @param HydratorFactoryInterface  $hydratorFactory
     * @param ThrottleSettingsInterface $defaultSettings
     */
    public function __construct(
        ThrottlerFactoryInterface $throttlerFactory,
        HydratorFactoryInterface $hydratorFactory,
        ThrottleSettingsInterface $defaultSettings
    ) {
        $this->throttlerFactory = $throttlerFactory;
        $this->hydratorFactory = $hydratorFactory;
        $this->defaultSettings = $defaultSettings;
    }

    /**
     * @inheritdoc
     */
    public function get($data, ThrottleSettingsInterface $settings = null)
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Invalid data, please check the data.');
        }

        $object = $this->hydratorFactory->make($data)->hydrate($data);

        if (!isset($this->throttlers[$object->getKey()])) {
            $this->throttlers[$object->getKey()] = $this->createThrottler($object, $settings);
        }

        return $this->throttlers[$object->getKey()];
    }

    /**
     * @param Data                           $object
     * @param ThrottleSettingsInterface|null $settings
     *
     * @return ThrottlerInterface
     */
    private function createThrottler(Data $object, ThrottleSettingsInterface $settings = null)
    {
        if (null === $settings) {
            $settings = $this->defaultSettings;
        } else {
            try {
                $settings = $this->defaultSettings->merge($settings);
            } catch (\InvalidArgumentException $exception) {
            }
        }

        return $this->throttlerFactory->make($object, $settings);
    }
}
