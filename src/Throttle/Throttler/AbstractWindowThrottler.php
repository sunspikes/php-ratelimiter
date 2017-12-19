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

namespace Sunspikes\Ratelimit\Throttle\Throttler;

use Sunspikes\Ratelimit\Cache\ThrottlerCacheInterface;
use Sunspikes\Ratelimit\Throttle\Settings\AbstractWindowSettings;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

abstract class AbstractWindowThrottler implements RetriableThrottlerInterface, \Countable
{
    /**
     * @var ThrottlerCacheInterface
     */
    protected $cache;

    /**
     * @var AbstractWindowSettings
     */
    protected $settings;

    /**
     * @var TimeAdapterInterface
     */
    protected $timeProvider;

    /**
     * AbstractWindowThrottler constructor.
     *
     * @param ThrottlerCacheInterface $cache
     * @param AbstractWindowSettings  $settings
     * @param TimeAdapterInterface    $timeAdapter
     */
    public function __construct(ThrottlerCacheInterface $cache, AbstractWindowSettings $settings, TimeAdapterInterface $timeAdapter)
    {
        $this->cache = $cache;
        $this->settings = $settings;
        $this->timeProvider = $timeAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function access(): bool
    {
        $status = $this->check();
        $this->hit();

        return $status;
    }

    /**
     * {@inheritdoc}
     */
    public function check(): bool
    {
        return $this->count() < $this->settings->getHitLimit();
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeLimit(): int
    {
        return $this->settings->getTimeLimit();
    }

    /**
     * {@inheritdoc}
     */
    public function getHitLimit(): int
    {
        return $this->settings->getHitLimit();
    }

    /**
     * {@inheritdoc}
     */
    abstract public function hit();

    /**
     * {@inheritdoc}
     */
    abstract public function count(): int;
}
