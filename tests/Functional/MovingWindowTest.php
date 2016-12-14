<?php

namespace Sunspikes\Tests\Functional;

use Mockery as M;
use Sunspikes\Ratelimit\Cache\Adapter\DesarrollaCacheAdapter;
use Sunspikes\Ratelimit\Cache\Factory\DesarrollaCacheFactory;
use Sunspikes\Ratelimit\RateLimiter;
use Sunspikes\Ratelimit\Throttle\Factory\TimeAwareThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Hydrator\HydratorFactory;
use Sunspikes\Ratelimit\Throttle\Settings\MovingWindowSettings;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

class MovingTest extends \PHPUnit_Framework_TestCase
{
    const TIME_LIMIT = 27;
    const TOKEN_LIMIT = 3;    //3 requests per 27 seconds

    /**
     * @var TimeAdapterInterface|M\MockInterface
     */
    private $timeAdapter;

    /**
     * @var Ratelimiter
     */
    private $ratelimiter;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->timeAdapter = M::mock(TimeAdapterInterface::class);
        $this->timeAdapter->shouldReceive('now')->andReturn(time());

        $cacheFactory = new DesarrollaCacheFactory(null, [
            'driver' => 'memory',
            'memory' => ['limit' => 10],
        ]);

        $this->ratelimiter = new RateLimiter(
            new TimeAwareThrottlerFactory(new DesarrollaCacheAdapter($cacheFactory->make()), $this->timeAdapter),
            new HydratorFactory(),
            new MovingWindowSettings(self::TOKEN_LIMIT, self::TIME_LIMIT)
        );
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

    public function testClear()
    {
        $throttle = $this->ratelimiter->get('clear-test');
        $throttle->hit();
        $throttle->clear();

        self::assertEquals(0, $throttle->count());
    }
}
