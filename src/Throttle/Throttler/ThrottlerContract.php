<?php

namespace Sunspikes\Ratelimit\Throttle\Throttler;

interface ThrottlerContract
{
    public function access();

    public function hit();

    public function clear();

    public function count();

    public function check();
}