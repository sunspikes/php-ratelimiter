<?php

namespace Sunspikes\Tests\Ratelimit\Functional;

use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Cache\Factory\DesarrollaCacheFactory;
use Sunspikes\Ratelimit\Cache\Factory\FactoryInterface;
use Sunspikes\Ratelimit\RateLimiter;

abstract class AbstractThrottlerTestCase extends TestCase
{
    protected Ratelimiter $ratelimiter;

    protected function setUp(): void
    {
        $cacheFactory = new DesarrollaCacheFactory(null, [
            'driver' => 'memory',
            'memory' => ['limit' => 10],
        ]);

        $this->ratelimiter = $this->createRatelimiter($cacheFactory);
    }

    public function testThrottlePreLimit(): void
    {
        $throttle = $this->ratelimiter->get('pre-limit-test');

        for ($i = 0; ++$i < $this->getMaxAttempts();) {
            $throttle->hit();
        }

        $this->assertTrue($throttle->check());
    }

    public function testThrottlePostLimit(): void
    {
        $throttle = $this->ratelimiter->get('post-limit-test');

        for ($i = 0; $i < $this->getMaxAttempts(); $i++) {
            $throttle->hit();
        }

        $this->assertFalse($throttle->check());
    }

    public function testThrottleAccess(): void
    {
        $throttle = $this->ratelimiter->get('access-test');

        for ($i = 0; $i < $this->getMaxAttempts(); $i++) {
            $throttle->access();
        }

        $this->assertFalse($throttle->access());
    }

    public function testThrottleCount(): void
    {
        $throttle = $this->ratelimiter->get('count-test');

        for ($i = 0; $i < $this->getMaxAttempts(); $i++) {
            $throttle->access();
        }

        $this->assertEquals(3, $throttle->count());
    }

    public function testClear(): void
    {
        $throttle = $this->ratelimiter->get('clear-test');
        $throttle->hit();
        $throttle->clear();

        self::assertEquals(0, $throttle->count());
    }

    protected function getMaxAttempts(): int
    {
        return 3;
    }

    abstract protected function createRatelimiter(FactoryInterface $cacheFactory): RateLimiter;
}
