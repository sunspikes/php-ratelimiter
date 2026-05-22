PHP Ratelimiter
===============

A framework independent, flexible and highly extensible rate limiter for PHP.

[![Latest Stable Version](https://poser.pugx.org/sunspikes/php-ratelimiter/v/stable)](https://packagist.org/packages/sunspikes/php-ratelimiter)
[![License](https://poser.pugx.org/sunspikes/php-ratelimiter/license)](https://packagist.org/packages/sunspikes/php-ratelimiter)

## Requirements

- PHP 8.0 or higher
- [desarrolla2/cache](https://github.com/desarrolla2/Cache) ^3.0

## Installation

```bash
composer require sunspikes/php-ratelimiter
```

## Usage

### Overview

```php
use Sunspikes\Ratelimit\Cache\Adapter\DesarrollaCacheAdapter;
use Sunspikes\Ratelimit\Cache\Factory\DesarrollaCacheFactory;
use Sunspikes\Ratelimit\RateLimiter;
use Sunspikes\Ratelimit\Throttle\Factory\ThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Hydrator\HydratorFactory;
use Sunspikes\Ratelimit\Throttle\Settings\ElasticWindowSettings;

// 1. Make a rate limiter with limit 3 attempts in 10 minutes
$cacheAdapter = new DesarrollaCacheAdapter((new DesarrollaCacheFactory())->make());
$settings = new ElasticWindowSettings(3, 600);
$ratelimiter = new RateLimiter(new ThrottlerFactory($cacheAdapter), new HydratorFactory(), $settings);

// 2. Get a throttler for path /login
$loginThrottler = $ratelimiter->get('/login');

// 3. Register a hit
$loginThrottler->hit();

// 4. Check if it reached the limit
if ($loginThrottler->check()) {
    // access permitted
} else {
    // access denied
}

// Or combine steps 3 & 4
if ($loginThrottler->access()) {
    // access permitted
} else {
    // access denied
}

// To get the number of hits
print $loginThrottler->count();
```

### Configuration

By default PHP Ratelimiter uses the [desarrolla2 cache adapter](https://github.com/desarrolla2/Cache), with sample configuration in `config/config.php`.

You can configure the drivers in `config.php`, for example to use memcached change the driver to `'memcache'`:

```php
return [
    'default_ttl' => 3600,
    'driver'      => 'memcache',
    'memcache' => [
        'servers' => ['localhost'],
    ],
];
```

Supported drivers: `memory`, `file`, `apc`/`apcu`, `memcache`, `redis`, `mysql`/`mysqli`, `mongo`/`mongodb`, `notcache`.

### Extending

#### Custom Cache Adapters

You can use any cache backend by implementing `Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface`:

```php
use Sunspikes\Ratelimit\Cache\Adapter\CacheAdapterInterface;
use Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException;

class SymfonyCacheAdapter implements CacheAdapterInterface
{
    public function __construct(
        private CacheItemPoolInterface $pool
    ) {}

    public function get(string $key): mixed { /* ... */ }
    public function set(string $key, mixed $value, ?int $ttl = null): void { /* ... */ }
    public function delete(string $key): void { /* ... */ }
    public function has(string $key): bool { /* ... */ }
    public function clear(): void { /* ... */ }
}
```

#### Custom Hydrators

You can have custom hydrators by implementing `Sunspikes\Ratelimit\Throttle\Hydrator\DataHydratorInterface`.

For example to use a Symfony Request object for rate limiting:

```php
use Sunspikes\Ratelimit\Throttle\Entity\Data;
use Sunspikes\Ratelimit\Throttle\Hydrator\DataHydratorInterface;

class RequestHydrator implements DataHydratorInterface
{
    public function hydrate(mixed $data): Data
    {
        $key = $data->getClientIp() . $data->getPathInfo();

        return new Data($key);
    }
}
```

Then decorate or extend the `HydratorFactory` to recognize your data:

```php
use Sunspikes\Ratelimit\Throttle\Hydrator\FactoryInterface;
use Sunspikes\Ratelimit\Throttle\Hydrator\DataHydratorInterface;
use Symfony\Component\HttpFoundation\Request;

class MyHydratorFactory implements FactoryInterface
{
    public function __construct(
        private FactoryInterface $defaultFactory
    ) {}

    public function make(mixed $data): DataHydratorInterface
    {
        if ($data instanceof Request) {
            return new RequestHydrator();
        }

        return $this->defaultFactory->make($data);
    }
}
```

## Throttler Types

### Elastic Window
An elastic window throttler will allow X requests in Y seconds. Any further access attempts will be counted, but return false as status. The window is extended with Y seconds on every hit, so there must be no hits during Y seconds for the counter to reset to 0.

See [Overview example](#overview) for instantiation.

### Time-based Throttlers
The following throttlers use time functions, requiring a different factory for construction:

```php
use Sunspikes\Ratelimit\Throttle\Factory\TimeAwareThrottlerFactory;
use Sunspikes\Ratelimit\Time\PhpTimeAdapter;

$cacheAdapter = new DesarrollaCacheAdapter((new DesarrollaCacheFactory())->make());
$timeAdapter = new PhpTimeAdapter();

$throttlerFactory = new TimeAwareThrottlerFactory($cacheAdapter, $timeAdapter);
$hydratorFactory = new HydratorFactory();

// $settings = ...
$ratelimiter = new RateLimiter($throttlerFactory, $hydratorFactory, $settings);
```

#### Fixed Window
Allows X requests in the Y seconds since the first request. The window does not extend.

```php
use Sunspikes\Ratelimit\Throttle\Settings\FixedWindowSettings;

// 120 attempts per minute
$settings = new FixedWindowSettings(120, 60);
```

#### Moving Window
Allows X requests during the previous Y seconds. The window is never extended beyond Y seconds.

```php
use Sunspikes\Ratelimit\Throttle\Settings\MovingWindowSettings;

// 120 attempts per minute
$settings = new MovingWindowSettings(120, 60);
```

#### Leaky Bucket
A [leaky bucket](https://en.wikipedia.org/wiki/Leaky_bucket) throttler allows X requests divided over time Y.

Any access attempts past threshold T (default: 0) will be delayed by Y / (X - T).

`access()` returns false if delayed, `hit()` returns the number of milliseconds waited.

**Note: Time limit for this throttler is in milliseconds, where it is seconds for other throttler types.**

```php
use Sunspikes\Ratelimit\Throttle\Settings\LeakyBucketSettings;

// 120 attempts per minute, start delaying after 30 requests
$settings = new LeakyBucketSettings(120, 60000, 30);
```

#### Retrial Queue
Wraps another throttler. When a hit would fail on the internal throttler, the request is delayed until the internal throttler has capacity again.

```php
use Sunspikes\Ratelimit\Throttle\Settings\RetrialQueueSettings;

// Leaky bucket that delays any overflow
$settings = new RetrialQueueSettings(new LeakyBucketSettings(120, 60000, 120));
```

## Author

Krishnaprasad MG [@sunspikes]

## Contributing

Please feel free to send pull requests.

## License

This is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
