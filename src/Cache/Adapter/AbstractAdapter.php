<?php

namespace Sunspikes\Ratelimit\Cache\Adapter;

use Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException;

class AbstractAdapter implements AdapterContract
{
    protected $ttl = 600; // 10 mins

    protected $cache;

    public function get($key)
    {
        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        throw new ItemNotFoundException('Cannot find the item in cache');
    }

    public function set($key, $value, $ttl = null)
    {
        $this->cache->set($key, $value, $ttl);
    }

    public function delete($key)
    {
        $this->cache->delete($key);
    }

    public function has($key)
    {
        return $this->cache->has($key);
    }

    public function clear()
    {
        $this->cache->clear();
    }

}