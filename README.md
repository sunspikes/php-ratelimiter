PHP Ratelimiter
===============

A framework independent rate limiter for PHP

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/51be0137-1158-403a-9fc7-ab863f2c0ca9/big.png)](https://insight.sensiolabs.com/projects/51be0137-1158-403a-9fc7-ab863f2c0ca9)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunspikes/php-ratelimiter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunspikes/php-ratelimiter/?branch=master)
[![Build Status](https://travis-ci.org/sunspikes/php-ratelimiter.svg?branch=master)](https://travis-ci.org/sunspikes/php-ratelimiter)

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

```php
<?php

include 'vendor/autoload.php';

$config = './vendor/sunspikes/php-ratelimiter/config/config.php';

// Make a rate limiter with limit 3 attempts in 10 minutes
$ratelimiter = new RateLimiter($config, 3, 600);

// Get a throttler for /login 
$throttler = $ratelimiter->get('/login');

// Access the resource, will increment the hit count
$throttler->access(); // or do $throttler->hit();

// Check if it reached the limit
if ($throttler->check()) {
    // access permitted
} else {
    // access denied
}

// Get the number of hits
print count($throttler); // or $throttler->count()


```
 
TODO
----
- More Tests
- More Documentation
