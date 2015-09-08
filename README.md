PHP Ratelimiter
===============

A framework independent, flexible and highly extensible rate limiter for PHP.

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/51be0137-1158-403a-9fc7-ab863f2c0ca9/small.png)](https://insight.sensiolabs.com/projects/51be0137-1158-403a-9fc7-ab863f2c0ca9)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunspikes/php-ratelimiter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunspikes/php-ratelimiter/?branch=master)
[![Build Status](https://travis-ci.org/sunspikes/php-ratelimiter.svg?branch=master)](https://travis-ci.org/sunspikes/php-ratelimiter)
[![API DOCS](http://apigenerator.org/badge.png)](http://sunspikes.github.io/php-ratelimiter/)

## Installation

### With Composer

It is best installed it through [packagist](http://packagist.org/packages/sunspikes/php-ratelimiter) 
by including `sunspikes/php-ratelimiter` in your project composer.json require:

``` json
    "require": {
        "sunspikes/php-ratelimiter":  "dev-master"
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
$ratelimiter = new RateLimiter(3, 600);

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

### Configuration

By default PHP Ratelimiter uses the [desarolla2 cache adapter](https://github.com/desarrolla2/Cache), the sample configuration provided in ```config/config.php```

You can configure the drivers in ```config.php```, for example to use memcache change the driver to ```'memcache'```

```php
return [
    'adapter'    => 'desarrolla',
    'desarrolla' => [
        'default_ttl' => 3600,
        'driver'      => 'memcache',
        //....
    ]
];
```

### Extending

The PHP Ratelimiter is highly extensible, you can have custom adapters by implementing ```Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface``` 

For example to use Doctrine cache adapter

```php
class DoctrineCacheAdapter implements CacheAdapterInterface
{
    public function __construct($cache)
    {
        $this->cache = $cache;
    }
    
    // Implement the methods
}

// Build adapter using APC cache driver
$adapter = new DoctrineCacheAdapter(new \Doctrine\Common\Cache\ApcCache());
```

Also you can have custom hydrators by extending ```Sunspikes\Ratelimit\Throttle\Hydrator\DataHydratorInterface```

For example to use a Symfony Request object instead of custom URL for ratelimiting

```php
class RequestHydrator implements DataHydratorInterface
{
    public function hydrate($data, $limit, $ttl)
    {
        $key = $data->getClientIp() . $data->getPathInfo();

        return new Data($key, $limit, $ttl);
    }
}

// Hydrate the request to Data object
$hydrator = new RequestHydrator();
$data = $hydrator->hydrate(new \Symfony\Component\HttpFoundation\Request(), 3, 600);

$factory = new ThrottlerFactory();
$requestThrottler = $factory->make($data, $adapter);

// Now you have the request throttler
```
## Author

Krishnaprasad MG [@sunspikes]

## Contributing

Please feel free to send pull requests.

## License

This is an open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
