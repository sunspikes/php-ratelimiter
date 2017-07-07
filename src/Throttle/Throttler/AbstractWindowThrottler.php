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

namespace Sunspikes\Ratelimit\Throttle\Throttler;

use Sunspikes\Ratelimit\Throttle\Entity\Data;
use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;
use Sunspikes\Ratelimit\Time\TimeProviderInterface;
use Sunspikes\src\Throttle\Cache\ThrottlerCacheInterface;

abstract class AbstractWindowThrottler implements \Countable
{
    /**
     * @var ThrottlerCacheInterface
     */
    protected $throttlerCache;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @var TimeProviderInterface
     */
    protected $timeProvider;

    /**
     * @var ThrottleSettingsInterface
     */
    protected $settings;

    /**
     * @var int
     */
    protected $counter;

    /**
     * @param ThrottlerCacheInterface   $throttlerCache
     * @param Data                      $data
     * @param TimeProviderInterface     $timeProvider
     * @param ThrottleSettingsInterface $settings
     */
    public function __construct(ThrottlerCacheInterface $throttlerCache, Data $data, TimeProviderInterface $timeProvider, ThrottleSettingsInterface $settings)
    {
        $this->throttlerCache = $throttlerCache;
        $this->data = $data;
        $this->timeProvider = $timeProvider;
        $this->settings = $settings;
        $this->counter = 0;
    }

    /**
     * @inheritdoc
     */
    public function access(): bool
    {
        $status = $this->check();

        $this->hit();

        return $status;
    }

    /**
     * @inheritdoc
     */
    public function clear(): ThrottlerInterface
    {
        $this->counter = 0;
        $this->throttlerCache->remove($this->data->getKey());

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        $this->counter = $this->throttlerCache->count($this->data->getKey());

        return $this->counter;
    }

    /**
     * @inheritdoc
     */
    public function check(): bool
    {
        return (! $this->throttlerCache->isExpired($this->data->getKey()))
            && ($this->throttlerCache->count($this->data->getKey()) < $this->settings->getLimit());
    }
}
