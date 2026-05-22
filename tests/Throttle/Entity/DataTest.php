<?php

namespace Sunspikes\Tests\Ratelimit\Throttle\Entity;

use PHPUnit\Framework\TestCase;
use Sunspikes\Ratelimit\Throttle\Entity\Data;

class DataTest extends TestCase
{
    private Data $data;

    protected function setUp(): void
    {
        $this->data = new Data('test');
    }

    public function testGetData(): void
    {
        $this->assertEquals('test', $this->data->getData());
    }

    public function testGetKey(): void
    {
        $this->assertEquals(sha1('test'), $this->data->getKey());
    }
}
