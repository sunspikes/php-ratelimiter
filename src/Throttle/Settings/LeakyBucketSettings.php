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

final class LeakyBucketSettings extends AbstractSettings implements ThrottleSettingsInterface
{
    /**
     * @var int|null
     */
    private $tokenLimit;

    /**
     * @var int|null
     */
    private $threshold;

    /**
     * @param int|null $tokenLimit
     * @param int|null $timeLimit In milliseconds
     * @param int|null $threshold
     * @param int|null $cacheTtl  In seconds
     */
    public function __construct(
        int $tokenLimit = null,
        int $timeLimit = null,
        int $threshold = null,
        int $cacheTtl = null
    ) {
        $this->tokenLimit = $tokenLimit;
        $this->timeLimit = $timeLimit;
        $this->threshold = $threshold ?? 0;
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * @inheritdoc
     */
    public function isValid(): bool
    {
        return null !== $this->tokenLimit && $this->isValidTimeLimit();
    }

    /**
     * @return int|null
     */
    public function getTokenLimit()
    {
        return $this->tokenLimit;
    }

    /**
     * @return int|null
     */
    public function getThreshold()
    {
        return $this->threshold;
    }
}
