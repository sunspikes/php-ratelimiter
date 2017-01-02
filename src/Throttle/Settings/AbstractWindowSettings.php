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

abstract class AbstractWindowSettings implements ThrottleSettingsInterface
{
    /**
     * @var int|null
     */
    private $hitLimit;

    /**
     * @var int|null
     */
    private $timeLimit;

    /**
     * @var int|null
     */
    private $cacheTtl;

    /**
     * @param int|null $tokenLimit
     * @param int|null $timeLimit  In seconds
     * @param int|null $cacheTtl   In seconds
     */
    public function __construct($tokenLimit = null, $timeLimit = null, $cacheTtl = null)
    {
        $this->hitLimit = $tokenLimit;
        $this->timeLimit = $timeLimit;
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * @inheritdoc
     */
    public function merge(ThrottleSettingsInterface $settings)
    {
        if (!$settings instanceof static) {
            throw new \InvalidArgumentException(
                sprintf('Unable to merge %s into %s', get_class($settings), get_class($this))
            );
        }

        return new static(
            null === $settings->getHitLimit() ? $this->hitLimit : $settings->getHitLimit(),
            null === $settings->getTimeLimit() ? $this->timeLimit : $settings->getTimeLimit(),
            null === $settings->getCacheTtl() ? $this->cacheTtl : $settings->getCacheTtl()
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
    public function getHitLimit()
    {
        return $this->hitLimit;
    }

    /**
     * @return int|null
     */
    public function getTimeLimit()
    {
        return $this->timeLimit;
    }

    /**
     * @return int|null
     */
    public function getCacheTtl()
    {
        return $this->cacheTtl;
    }
}
