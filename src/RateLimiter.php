<?php

namespace Sunspikes;

use Sunspikes\Ratelimit\Cache\Factory\DesarrollaCacheFactory;
use Sunspikes\Ratelimit\Throttle\Factory\ThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Hydrator\ArrayHydrator;
use Sunspikes\Ratelimit\Throttle\Hydrator\RequestHydrator;
use Sunspikes\Ratelimit\Throttle\Hydrator\StringHydrator;

class RateLimiter
{
    private $adapter;
    private $throttlers;

    public function __construct(array $config)
    {
        if ('desarrolla' == $config['adapter'])
        {
            $adapterFactory = new DesarrollaCacheFactory();
            $this->adapter = $adapterFactory->make($config);
        }

        throw new \InvalidArgumentException('No adapter found, please check your config.');
    }

    public function get($data, $limit, $ttl)
    {
        if (! empty($data))
        {
            if (is_array($data))
            {
                $data = new ArrayHydrator($data, $limit, $ttl);
            }
            elseif (is_string($data))
            {
                $data = new StringHydrator($data, $limit, $ttl);
            }
            else
            {
                throw new \InvalidArgumentException("Unsupported data, please check the data.");
            }

            if (isset($this->throttlers[$data->getKey()]))
            {
                $factory = new ThrottlerFactory();
                $this->throttlers[$data->getKey()] = $factory->make($data, $this->adapter);
            }
        }

        return $this->throttlers[$data->getKey()];
    }
}