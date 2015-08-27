<?php

namespace Sunspikes\Ratelimit\Throttle\Throttler;

use Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException;

class CacheThrottler implements ThrottlerContract, \Countable
{
    protected $cache;

    protected $key;

    protected $limit;

    protected $ttl;

    protected $counter;

    public function __contruct($cache, $key, $limit, $ttl)
    {
        $this->cache = $cache;
        $this->key = $key;
        $this->limit = $limit;
        $this->ttl = $ttl;
    }

    public function access()
    {
        $status = $this->check();

        $this->hit();

        return $status;
    }

    public function hit()
    {
        $this->counter = $this->count() + 1;

        $this->cache->set($this->key, $this->counter, $this->ttl);

        return $this;
    }

    public function clear()
    {
        $this->counter = 0;

        $this->cache->set($this->key, $this->counter, $this->ttl);

        return $this;
    }

    public function count()
    {
        if (! is_null($this->counter)) {
            return $this->counter;
        }

        try {
            $this->counter = $this->cache->get($this->key);
        }
        catch (ItemNotFoundException $e) {
            $this->counter = 0;
        }

        return $this->counter;
    }

    public function check()
    {
        return ($this->count() < $this->limit);
    }

    public function getCache()
    {
        return $this->cache;
    }
}