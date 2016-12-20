<?php

namespace Sunspikes\Tests\Functional;

use Mockery as M;
use Sunspikes\Ratelimit\Cache\Adapter\DesarrollaCacheAdapter;
use Sunspikes\Ratelimit\Cache\Factory\FactoryInterface;
use Sunspikes\Ratelimit\RateLimiter;
use Sunspikes\Ratelimit\Throttle\Factory\TimeAwareThrottlerFactory;
use Sunspikes\Ratelimit\Throttle\Hydrator\HydratorFactory;
use Sunspikes\Ratelimit\Throttle\Settings\MovingWindowSettings;
use Sunspikes\Ratelimit\Time\TimeAdapterInterface;

class MovingWindowTest extends AbstractThrottlerTestCase
{
    const TIME_LIMIT = 24;

    /**
     * @var int
     */
    private $startTime;

    /**
     * @var TimeAdapterInterface|M\MockInterface
     */
    private $timeAdapter;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->timeAdapter = M::mock(TimeAdapterInterface::class);
        $this->timeAdapter->shouldReceive('now')->andReturn($this->startTime = time())->byDefault();

        parent::setUp();
    }

    public function testWindowMoves()
    {
        $throttle = $this->ratelimiter->get('window-moves');

        $timeValues = [];

        for ($i = 0; $i < $this->getMaxAttempts(); $i++) {
            $timeValues[] = $this->startTime + $i;
            $timeValues[] = $this->startTime + $i;
        }

        $timeValues[] = $this->startTime + self::TIME_LIMIT + 1;
        $timeValues[] = $this->startTime + self::TIME_LIMIT + 1;

        $this->timeAdapter->shouldReceive('now')->andReturnValues($timeValues);

        for ($i = 0; $i < $this->getMaxAttempts() + 1; $i++) {
            $throttle->hit();
        }

        // First hit should have expired
        self::assertEquals($this->getMaxAttempts(), $throttle->count());
    }

    /**
     * @inheritdoc
     */
    protected function createRatelimiter(FactoryInterface $cacheFactory)
    {
        return new RateLimiter(
            new TimeAwareThrottlerFactory(new DesarrollaCacheAdapter($cacheFactory->make()), $this->timeAdapter),
            new HydratorFactory(),
            new MovingWindowSettings($this->getMaxAttempts(), self::TIME_LIMIT)
        );
    }
}
