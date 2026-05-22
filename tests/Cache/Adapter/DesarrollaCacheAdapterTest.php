<?php

namespace Sunspikes\Tests\Ratelimit\Cache\Adapter;

use Desarrolla2\Cache\CacheInterface;
use Mockery as M;
use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Cache\Adapter\DesarrollaCacheAdapter;
use Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException;

class DesarrollaCacheAdapterTest extends TestCase
{
    private DesarrollaCacheAdapter $adapterMock;

    protected function setUp(): void
    {
        $cache = M::mock(CacheInterface::class);

        $cache->shouldReceive('set')
            ->with('key', 'value', 30)
            ->andReturnNull();

        $cache->shouldReceive('get')
            ->with('key')
            ->andReturn('value');

        $cache->shouldReceive('get')
            ->with('non-existing-key')
            ->andThrow(new ItemNotFoundException());

        $cache->shouldReceive('has')
            ->with('non-existing-key')
            ->andReturn(false);

        $cache->shouldReceive('has')
            ->with('key')
            ->andReturn(true);

        $cache->shouldReceive('has')
            ->with('has-existing-key')
            ->andReturn(true);

        $cache->shouldReceive('has')
            ->with('has-nonexisting-key')
            ->andReturn(false);

        $cache->shouldReceive('delete')
            ->with('delete-key')
            ->andReturnNull();

        $cache->shouldReceive('clear')
            ->withNoArgs()
            ->andReturnNull();

        $this->adapterMock = new DesarrollaCacheAdapter($cache);
    }

    public function testSet(): void
    {
        $this->adapterMock->set('key', 'value', 30);
        $this->assertEquals('value', $this->adapterMock->get('key'));
    }

    public function testGet(): void
    {
        $this->assertEquals('value', $this->adapterMock->get('key'));
    }

    public function testGetNonExisting(): void
    {
        $this->expectException(ItemNotFoundException::class);
        $this->adapterMock->get('non-existing-key');
    }

    public function testHasExisting(): void
    {
        $this->assertTrue($this->adapterMock->has('has-existing-key'));
    }

    public function testHasNonExisting(): void
    {
        $this->assertFalse($this->adapterMock->has('has-nonexisting-key'));
    }

    public function testDelete(): void
    {
        $this->adapterMock->delete('delete-key');
        $this->assertTrue(true);
    }

    public function testClear(): void
    {
        $this->adapterMock->clear();
        $this->assertTrue(true);
    }
}
