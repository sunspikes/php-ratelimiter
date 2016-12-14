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

use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

final class RetrialQueueThrottler implements ThrottlerInterface
{
    /**
     * @var ThrottlerInterface
     */
    private $internalThrottler;

    /**
     * @var TimeAdapterInterface
     */
    private $timeProvider;

    /**
     * @param ThrottlerInterface   $internalThrottler
     * @param TimeAdapterInterface $timeProvider
     */
    public function __construct(ThrottlerInterface $internalThrottler, TimeAdapterInterface $timeProvider)
    {
        $this->internalThrottler = $internalThrottler;
        $this->timeProvider = $timeProvider;
    }

    /**
     * @inheritdoc
     */
    public function access()
    {
        $status = $this->check();
        $this->hit();

        return $status;
    }

    /**
     * @inheritdoc
     */
    public function hit()
    {
        if (0 !== $waitTime = $this->getWaitTime()) {
            $this->timeProvider->usleep(1e6 * $waitTime);
        }

        return $this->internalThrottler->hit();
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->internalThrottler->clear();
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->internalThrottler->count();
    }

    /**
     * @inheritdoc
     */
    public function check()
    {
        return $this->internalThrottler->check();
    }

    /**
     * @inheritdoc
     */
    public function getTime()
    {
        return $this->internalThrottler->getTime();
    }

    /**
     * @inheritdoc
     */
    public function getLimit()
    {
        return $this->internalThrottler->getLimit();
    }

    /**
     * @return int time in seconds
     */
    private function getWaitTime()
    {
        $hitLimit = $this->internalThrottler->getLimit();
        $hitCount = $this->internalThrottler->count();

        if ($hitCount < $hitLimit) {
            return 0;
        }

        if ($this->internalThrottler instanceof ElasticWindowThrottler) {
            return $this->internalThrottler->getTime();
        }

        $waitTime = (1 + $hitCount - $hitLimit) * ($this->internalThrottler->getTime() / $hitLimit);

        if ($this->internalThrottler instanceof LeakyBucketThrottler) {
            $waitTime /= 1e3;
        }

        return $waitTime;
    }
}
