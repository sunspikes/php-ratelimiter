<?php

namespace Sunspikes\Ratelimit\Throttle\Entity;

class Data
{
    protected $data;

    protected $limit;

    protected $ttl;

    protected $key;

    public function __construct($data, $limit, $ttl)
    {
        $this->data = $data;
        $this->limit = $limit;
        $this->ttl = $ttl;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getTtl()
    {
        return $this->ttl;
    }

    public function getKey()
    {
        if (is_null($this->key)) {
            $this->key = sha1($this->data);
        }

        return $this->key;
    }
}