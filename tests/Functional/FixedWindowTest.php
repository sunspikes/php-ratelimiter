<?php

namespace Sunspikes\Tests\Functional;

use Sunspikes\Ratelimit\Cache\Adapter\DesarrollaCacheAdapter;
use Sunspikes\Ratelimit\Cache\Factory\DesarrollaCacheFactory;
use Sunspikes\Ratelimit\RateLimiter;
use Sunspikes\Ratelimit\Throttle\Factory\ThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Hydrator\HydratorFactory;
use Sunspikes\Ratelimit\Throttle\Settings\FixedWindowSettings;
use Sunspikes\Ratelimit\Time\PhpTimeAdapter;

class FixedWindowTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Ratelimiter
     */
    private $ratelimiter;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $cacheFactory = new DesarrollaCacheFactory(null, [
            'driver' => 'memory',
            'memory' => ['limit' => 10],
        ]);

        $this->ratelimiter = new RateLimiter(
            new ThrottlerFactory(
                new DesarrollaCacheAdapter($cacheFactory->make()),
                new PhpTimeAdapter()
            ),
            new HydratorFactory(),
            new FixedWindowSettings(3, 600)
        );
    }

    public function testThrottlePreLimit()
    {
        $throttle = $this->ratelimiter->get('pre-limit-test');
        $throttle->hit();
        $throttle->hit();

        self::assertTrue($throttle->check());
    }

    public function testThrottlePostLimit()
    {
        $throttle = $this->ratelimiter->get('post-limit-test');
        $throttle->hit();
        $throttle->hit();
        $throttle->hit();

        self::assertFalse($throttle->check());
    }

    public function testThrottleAccess()
    {
        $throttle = $this->ratelimiter->get('access-test');
        $throttle->access();
        $throttle->access();
        $throttle->access();

        self::assertFalse($throttle->access());
    }

    public function testThrottleCount()
    {
        $throttle = $this->ratelimiter->get('count-test');
        $throttle->access();
        $throttle->access();
        $throttle->access();

        self::assertEquals(3, $throttle->count());
    }

    public function testClear()
    {
        $throttle = $this->ratelimiter->get('clear-test');
        $throttle->hit();
        $throttle->clear();

        self::assertEquals(0, $throttle->count());
    }
}
