<?php
namespace x3tech\LaravelShipper\Test;

use x3tech\LaravelShipper\CompatBridge;

use PHPUnit_Framework_TestCase;
use Mockery as m;

class CompatBridgeTest extends PHPUnit_Framework_TestCase
{
    public function testReturnsCorrectConfig()
    {
        $app = m::mock('\Illuminate\Foundation\Application')
            ->shouldReceive('environment')
            ->andReturn('production')
            ->getMock();
        $config = m::mock('Illuminate\Config\Repository')
            ->shouldReceive('get')
            ->with('shipper', null)
            ->andReturn(array('5'))
            ->getMock()
            ->shouldReceive('get')
            ->with('shipper::config', null)
            ->andReturn(array('4'))
            ->getMock();

        $compat4 = new CompatBridge('4.2.1', $app, $config);
        $compat5 = new CompatBridge('5.1.0', $app, $config);

        $this->assertEquals(array('4'), $compat4->getShipperConfig());
        $this->assertEquals(array('5'), $compat5->getShipperConfig());
    }
}
