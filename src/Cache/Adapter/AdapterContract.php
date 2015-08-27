<?php

namespace Sunspikes\Ratelimit\Cache\Adapter;

interface AdapterContract
{
    public function get($key);

    public function set($key, $value, $ttl = null);

    public function delete($key);

    public function has($key);

    public function clear();
}