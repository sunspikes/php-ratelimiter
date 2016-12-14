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
use Sunspikes\Ratelimit\Throttle\Settings\ElasticWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;
use Sunspikes\Ratelimit\Throttle\Throttler\ElasticWindowThrottler;

class ThrottlerFactory implements FactoryInterface
{
    /**
     * @var CacheAdapterInterface
     */
    protected $cacheAdapter;

    /**
     * @param CacheAdapterInterface $cacheAdapter
     */
    public function __construct(CacheAdapterInterface $cacheAdapter)
    {
        $this->cacheAdapter = $cacheAdapter;
    }

    /**
     * @inheritdoc
     */
    public function make(Data $data, ThrottleSettingsInterface $settings)
    {
        if (!$settings->isValid()) {
            throw new \InvalidArgumentException('Provided throttler settings not valid');
        }

        return $this->createThrottler($data, $settings);
    }

    /**
     * @param Data                      $data
     * @param ThrottleSettingsInterface $settings
     *
     * @return ElasticWindowThrottler
     */
    protected function createThrottler(Data $data, ThrottleSettingsInterface $settings)
    {
        if ($settings instanceof ElasticWindowSettings) {
            return new ElasticWindowThrottler(
                $this->cacheAdapter,
                $data->getKey(),
                $settings->getLimit(),
                $settings->getTime()
            );
        }

        throw new \InvalidArgumentException(
            sprintf('Unable to create throttler for %s settings', get_class($settings))
        );
    }
}
