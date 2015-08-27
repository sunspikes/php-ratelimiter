<?php

namespace Sunspikes\Ratelimit\Cache\Factory;

interface FactoryContract
{
    public function make($config);
}