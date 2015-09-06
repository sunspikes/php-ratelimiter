<?php

namespace Sunspikes\Tests\Ratelimit;

use Mockery as M;
use Sunspikes\Ratelimit\RateLimiter;

class RatelimiterTest extends \PHPUnit_Framework_TestCase
{
    private $ratelimiter;

    public function setUp()
    {
        $this->ratelimiter = new RateLimiter(3, 600);
    }

    public function testThrottlePreLimit()
    {
        $throttle = $this->ratelimiter->get('pre-limit-test');
        $throttle->hit();
        $throttle->hit();

        $this->assertTrue($throttle->check());
    }

    public function testThrottlePostLimit()
    {
        $throttle = $this->ratelimiter->get('post-limit-test');
        $throttle->hit();
        $throttle->hit();
        $throttle->hit();

        $this->assertFalse($throttle->check());
    }

    public function testThrottleAccess()
    {
        $throttle = $this->ratelimiter->get('access-test');
        $throttle->access();
        $throttle->access();
        $throttle->access();

        $this->assertFalse($throttle->access());
    }

    public function testThrottleCount()
    {
        $throttle = $this->ratelimiter->get('count-test');
        $throttle->access();
        $throttle->access();
        $throttle->access();

        $this->assertEquals(3, $throttle->count());
    }
}