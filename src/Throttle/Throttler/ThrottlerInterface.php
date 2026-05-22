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

declare(strict_types=1);

namespace Sunspikes\Ratelimit\Throttle\Throttler;

interface ThrottlerInterface
{
    public const SECOND_TO_MILLISECOND_MULTIPLIER = 1000;
    public const MILLISECOND_TO_MICROSECOND_MULTIPLIER = 1000;

    /**
     * Access the resource and return status
     *
     * @return bool
     */
    public function access(): bool;

    /**
     * Register a hit for the resource
     *
     * @return mixed
     */
    public function hit(): mixed;

    /**
     * Clear the hit counter
     *
     * @return void
     */
    public function clear(): void;

    /**
     * Get the hit count
     *
     * @return int
     */
    public function count(): int;

    /**
     * Check the throttle status
     *
     * @return bool
     */
    public function check(): bool;

    /**
     * Get time window
     *
     * @return int
     */
    public function getTime(): int;

    /**
     * Get throttle limit
     *
     * @return int
     */
    public function getLimit(): int;
}
