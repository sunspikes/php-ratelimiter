<?php

namespace Sunspikes\Tests\Ratelimit;

use Mockery as M;
use Sunspikes\Ratelimit\RateLimiter;
use Sunspikes\Ratelimit\Throttle\Entity\Data;
use Sunspikes\Ratelimit\Throttle\Factory\FactoryInterface as ThrottlerFactoryInterface;
use Sunspikes\Ratelimit\Throttle\Hydrator\DataHydratorInterface;
use Sunspikes\Ratelimit\Throttle\Hydrator\FactoryInterface as HydratorFactoryInterface;
use Sunspikes\Ratelimit\Throttle\Settings\ElasticWindowSettings;
use Sunspikes\Ratelimit\Throttle\Settings\ThrottleSettingsInterface;
use Sunspikes\Ratelimit\Throttle\Throttler\ThrottlerInterface;

class RatelimiterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ThrottleSettingsInterface|M\MockInterface
     */
    private $defaultSettings;

    /**
     * @var ThrottlerFactoryInterface|M\MockInterface
     */
    private $throttlerFactory;

    /**
     * @var HydratorFactoryInterface|M\MockInterface
     */
    private $hydratorFactory;

    /**
     * @var Ratelimiter
     */
    private $ratelimiter;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->throttlerFactory = M::mock(ThrottlerFactoryInterface::class);
        $this->hydratorFactory =  M::mock(HydratorFactoryInterface::class);
        $this->defaultSettings = M::mock(ThrottleSettingsInterface::class);

        $this->ratelimiter = new RateLimiter(
            $this->throttlerFactory,
            $this->hydratorFactory,
            $this->defaultSettings
        );
    }

    public function testGetWithInvalidData()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $this->ratelimiter->get('');
    }

    public function testGetWithDefaultSettings()
    {
        $object = $this->getHydratedObject('key');

        $this->throttlerFactory
            ->shouldReceive('make')
            ->with($object, $this->defaultSettings)
            ->andReturn(M::mock(ThrottlerInterface::class));

        self::assertInstanceOf(ThrottlerInterface::class, $this->ratelimiter->get('key'));
    }

    public function testGetWithMergableSettings()
    {
        $object = $this->getHydratedObject('key');

        $this->defaultSettings->shouldReceive('merge')->once()->andReturn(M::mock(ThrottleSettingsInterface::class));

        $this->throttlerFactory
            ->shouldReceive('make')
            ->with($object, M::type(ThrottleSettingsInterface::class))
            ->andReturn(M::mock(ThrottlerInterface::class));

        self::assertInstanceOf(ThrottlerInterface::class, $this->ratelimiter->get('key', new ElasticWindowSettings()));
    }

    public function testGetWithUnmergableSettings()
    {
        $object = $this->getHydratedObject('key');

        $newSettings = M::mock(ThrottleSettingsInterface::class);
        $this->defaultSettings->shouldReceive('merge')->once()->andReturn($newSettings);

        $this->throttlerFactory
            ->shouldReceive('make')
            ->with($object, $newSettings)
            ->andReturn(M::mock(ThrottlerInterface::class));

        self::assertInstanceOf(ThrottlerInterface::class, $this->ratelimiter->get('key', $newSettings));
    }

    public function testGetThrottlerCaching()
    {
        $object1 = $this->getHydratedObject('key1');
        $object2 = $this->getHydratedObject('key2');

        $this->throttlerFactory
            ->shouldReceive('make')
            ->with($object1, M::type(ThrottleSettingsInterface::class))
            ->once()
            ->andReturn(M::mock(ThrottlerInterface::class));

        $this->throttlerFactory
            ->shouldReceive('make')
            ->with($object2, M::type(ThrottleSettingsInterface::class))
            ->once()
            ->andReturn(M::mock(ThrottlerInterface::class));

        self::assertSame($this->ratelimiter->get('key1'), $this->ratelimiter->get('key1'));
        self::assertNotSame($this->ratelimiter->get('key1'), $this->ratelimiter->get('key2'));
    }

    /**
     * @param string $key
     *
     * @return Data
     */
    private function getHydratedObject($key)
    {
        $object = new Data('data-'.$key);

        $dataHydrator = M::mock(DataHydratorInterface::class);
        $dataHydrator->shouldReceive('hydrate')->with($key)->andReturn($object);

        $this->hydratorFactory->shouldReceive('make')->with($key)->andReturn($dataHydrator);

        return $object;
    }
}
