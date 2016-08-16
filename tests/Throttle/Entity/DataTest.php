<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Entity;

use Sunspikes\Ratelimit\Throttle\Entity\Data;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Data
     */
    private $data;

    public function setUp()
    {
        $this->data = new Data('test');
    }

    public function testGetData()
    {
        $this->assertEquals('test', $this->data->getData());
    }

    public function testGetKey()
    {
        $this->assertEquals(sha1('test'), $this->data->getKey());
    }
}
