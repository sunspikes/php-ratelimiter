PHP Ratelimiter
===============

A framework independent rate limiter for PHP

```
<?php

include 'vendor/autoload.php';

$config = './vendor/sunspikes/php-ratelimiter/config/config.php';

$ratelimiter = new RateLimiter($config);

// Throttle /login for 3 attempts in 10 minutes
$throttler = $ratelimiter->get('/login', 3, 600);

// Check if it reached the limit
$throttler->check();

// Get the number of hits
print count($throttler); // or $throttler->count()

// Access the resource, will increment the hit count
$throttler->access(); // or do $throttler->hit();

```
 
TODO
----
- Tests
- Documentation
