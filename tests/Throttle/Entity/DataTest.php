<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Entity;

use Sunspikes\Ratelimit\Throttle\Entity\Data;

class DataTest extends \PHPUnit_Framework_TestCase
{
    private $data;

    public function setUp()
    {
        $this->data = new Data('test', 3, 60);
    }

    public function testGetData()
    {
        $this->assertEquals('test', $this->data->getData());
    }

    public function testGetLimit()
    {
        $this->assertEquals(3, $this->data->getLimit());
    }

    public function testGetTtl()
    {
        $this->assertEquals(60, $this->data->getTtl());
    }

    public function testGetKey()
    {
        $this->assertEquals(sha1('test'), $this->data->getKey());
    }
}
