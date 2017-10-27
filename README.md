PHP Ratelimiter
===============

A framework independent, flexible and highly extensible rate limiter for PHP.

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/51be0137-1158-403a-9fc7-ab863f2c0ca9/mini.png)](https://insight.sensiolabs.com/projects/51be0137-1158-403a-9fc7-ab863f2c0ca9)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunspikes/php-ratelimiter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunspikes/php-ratelimiter/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/sunspikes/php-ratelimiter/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sunspikes/php-ratelimiter/?branch=master)
[![Code Climate](https://codeclimate.com/github/sunspikes/php-ratelimiter/badges/gpa.svg)](https://codeclimate.com/github/sunspikes/php-ratelimiter)
[![Build Status](https://travis-ci.org/sunspikes/php-ratelimiter.svg?branch=master)](https://travis-ci.org/sunspikes/php-ratelimiter)
[![Latest Stable Version](https://poser.pugx.org/sunspikes/php-ratelimiter/v/stable)](https://packagist.org/packages/sunspikes/php-ratelimiter)
[![License](https://poser.pugx.org/sunspikes/php-ratelimiter/license)](https://packagist.org/packages/sunspikes/php-ratelimiter)

## Installation

### With Composer

It is best installed it through [packagist](http://packagist.org/packages/sunspikes/php-ratelimiter) 
by including `sunspikes/php-ratelimiter` in your project composer.json require:

``` json
    "require": {
        "sunspikes/php-ratelimiter":  "^2.0"
    }
```

### Without Composer

You can also download it from [Github] (https://github.com/sunspikes/php-ratelimiter), 
but no autoloader is provided so you'll need to register it with your own PSR-4 
compatible autoloader.

## Usage

### Overview

```php
// 1. Make a rate limiter with limit 3 attempts in 10 minutes
$throttlerCache = new ThrottlerCache($anyPsr6CacheAdapter); 
$settings = new ElasticWindowSettings(3, 600);
$ratelimiter = new RateLimiter(new ThrottlerFactory($throttlerCache), new HydratorFactory(), $settings);

// 2. Get a throttler for path /login 
$loginThrottler = $ratelimiter->get('/login');

// 3. Register a hit
$loginThrottler->hit()

// 4. Check if it reached the limit
if ($loginThrottler->check()) {
    // access permitted
} else {
    // access denied
}

// Or combine the steps 3 & 4
if ($loginThrottler->access()) {
    // access permitted
} else {
    // access denied
}

// To get the number of hits
print $loginThrottler->count(); // or count($throttler)
```

### Using and Extending

The PHP Ratelimiter is highly extensible, you can use any PSR6 cache adapters as caching backend

For example,

  - PHP Cache (http://www.php-cache.com/en/latest/) 
  - Symfony (https://symfony.com/doc/current/components/cache.html)
  - Stash (http://www.stashphp.com)

For example to use Memcache adapter from php cache

```php
$adapter = new \Cache\Adapter\Memcache\MemcacheCachePool();
$throttlerCache = new ThrottlerCache($adapter);
...
```

Also you can have custom hydrators by implementing ```Sunspikes\Ratelimit\Throttle\Hydrator\DataHydratorInterface```

For example to use a Symfony Request object instead of custom URL for ratelimiting

```php
class RequestHydrator implements DataHydratorInterface
{
    public function hydrate($data, $limit, $ttl)
    {
        // Make the key string
        $key = $data->getClientIp() . $data->getPathInfo();

        return new Data($key, $limit, $ttl);
    }
}

// Hydrate the request to Data object
$hydrator = new RequestHydrator();
```

Then decorate or extend the HydratorFactory to recognize your data

```php
use Hydrator\FactoryInterface;

class MyHydratorFactory implements FactoryInterface
{
    private $defaultFactory;

    public function __construct(FactoryInterface $defaultFactory)
    {
        $this->defaultFactory = $defaultFactory;
    }

    public function make($data)
    {
        if ($data instanceof Request) {
            return new RequestHydrator();
        }

        return $this->defaultFactory->make($data);
    }
}
```

## Throttler types

### Elastic Window
An elastic window throttler will allow X requests in Y seconds. Any further access attempts will be counted, but return false as status. Note that the window will be extended with Y seconds on every hit. This means there need to be no hits during Y seconds for the counter to be reset to 0. 

See [Overview example](#overview) for instantiation.

### Time-based throttlers
All the following throttlers use time functions, thus needing a different factory for construction:

```php
$throttlerCache = new ThrottlerCache($adapter);
$timeAdapter = new PhpTimeAdapter();

$throttlerFactory = new TimeAwareThrottlerFactory($throttlerCache, $timeAdapter);
$hydratorFactory = new HydratorFactory();

//$settings = ...
$ratelimiter = new RateLimiter($throttlerFactory, $hydratorFactory, $settings);
```

#### Fixed Window
A fixed window throttler will allow X requests in the Y seconds since the first request. Any further access attempts will be counted, but return false as status. The window will not be extended at all. 

```php
// Make a rate limiter with limit 120 attempts per minute
$settings = new FixedWindowSettings(120, 60);
```

#### Moving Window
A moving window throttler will allow X requests during the previous Y seconds. Any further access attempts will be counted, but return false as status. The window is never extended beyond Y seconds. 

```php
// Make a rate limiter with limit 120 attempts per minute
$settings = new MovingWindowSettings(120, 60);
```

#### Leaky Bucket
A [leaky bucket](https://en.wikipedia.org/wiki/Leaky_bucket) throttler will allow X requests divided over time Y.

Any access attempts past the threshold T (default: 0) will be delayed by Y / (X - T)

`access()` will return false if delayed, `hit()` will return the number of milliseconds waited

__Note: Time limit for this throttler is in milliseconds, where it is seconds for the other throttler types!__

```php
// Make a rate limiter with limit 120 attempts per minute, start delaying after 30 requests
$settings = new LeakyBucketSettings(120, 60000, 30);
```

#### Retrial Queue
The retrial queue encapsulates another throttler.
When this throttler receives a hit which would fail on the internal throttler, 
the request is delayed until the internal throttler has capacity again.   

```php
// Make a leaky bucket ratelimiter which delays any overflow
$settings = new RetrialQueueSettings(new LeakyBucketSettings(120, 60000, 120));
```

## Author

Krishnaprasad MG [@sunspikes]

@Feijs

## Contributing

Please feel free to send pull requests.

## License

This is an open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
