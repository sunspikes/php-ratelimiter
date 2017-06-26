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

namespace Sunspikes\Ratelimit\Throttle\Settings;

final class LeakyBucketSettings extends AbstractWindowSettings
{
    /**
     * @var int|null
     */
    private $threshold;

    /**
     * @param int|null $hitLimit
     * @param int|null $timeLimit  In milliseconds
     * @param int|null $cacheTtl   In seconds
     * @param int|null $threshold
     */
    public function __construct($hitLimit = null, $timeLimit = null, $cacheTtl = null, $threshold = null)
    {
        parent::__construct($hitLimit, $timeLimit, $cacheTtl);
        $this->threshold = $threshold;
    }

    /**
     * @inheritdoc
     */
    public function merge(ThrottleSettingsInterface $settings)
    {
        if (!$settings instanceof self) {
            throw new \InvalidArgumentException(
                sprintf('Unable to merge %s into %s', get_class($settings), get_class($this))
            );
        }

        return new self(
            $settings->getTokenLimit() ?? $this->hitLimit,
            $settings->getTimeLimit() ?? $this->timeLimit,
            $settings->getCacheTtl() ?? $this->cacheTtl,
            $settings->getThreshold() ?? $this->threshold
        );
    }

    /**
     * @inheritdoc
     */
    public function isValid()
    {
        return
            null !== $this->hitLimit &&
            null !== $this->timeLimit &&
            0 !== $this->timeLimit;
    }

    /**
     * @return int|null
     */
    public function getThreshold()
    {
        return $this->threshold;
    }
}
