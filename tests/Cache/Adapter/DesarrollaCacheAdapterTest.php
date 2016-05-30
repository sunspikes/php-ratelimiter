<?php

namespace Sunspikes\Tests\Ratelimit\Cache\Adapter;

use Sunspikes\Ratelimit\Cache\Adapter\DesarrollaCacheAdapter;
use Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException;
use Desarrolla2\Cache\CacheInterface;
use Mockery as M;

class DesarrollaCacheAdapterTest extends \PHPUnit_Framework_TestCase
{
    private $adapterMock;

    public function setUp()
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

        $cache->shouldReceive('clearCache')
            ->withNoArgs()
            ->andReturnNull();

        $this->adapterMock = new DesarrollaCacheAdapter($cache);
    }

    public function testSet()
    {
        $this->assertNull($this->adapterMock->set('key', 'value', 30));
    }

    public function testGet()
    {
        $this->assertEquals('value', $this->adapterMock->get('key'));
    }

    /**
     * @expectedException \Sunspikes\Ratelimit\Cache\Exception\ItemNotFoundException
     */
    public function testGetNonExisting()
    {
        $this->adapterMock->get('non-existing-key');
    }

    public function testHasExisting()
    {
        $this->assertTrue($this->adapterMock->has('has-existing-key'));
    }

    public function testHasNonExisting()
    {
        $this->assertFalse($this->adapterMock->has('has-nonexisting-key'));
    }

    public function testDelete()
    {
        $this->assertNull($this->adapterMock->delete('delete-key'));
    }

    public function testClear()
    {
        $this->assertNull($this->adapterMock->clear());
    }
}
