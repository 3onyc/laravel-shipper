<?php
namespace x3tech\LaravelShipper\Test\Builder;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use x3tech\LaravelShipper\Builder\FigBuilder;
use x3tech\LaravelShipper\Builder\BuildStep\FigBuildStepInterface;

class FigBuilderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $cls = 'x3tech\LaravelShipper\Builder\BuildStep\FigBuildStepInterface';
        $this->builder = new FigBuilder();

        $this->mockStep1 = m::mock($cls)
             ->shouldReceive('run')
             ->with(array())
             ->andReturn(array('foo'))
             ->getMock();

        $this->mockStep2 = m::mock($cls)
             ->shouldReceive('run')
             ->with(array('foo'))
             ->andReturn(array('foo', 'bar'))
             ->getMock();
    }
    public function testBuildOne()
    {
        $builder = new FigBuilder();
        $builder->addBuildStep($this->mockStep1);

        $this->assertEquals(array('foo'), $builder->build());
    }

    public function testBuildPriorities()
    {
        $builder = new FigBuilder();
        $builder->addBuildStep($this->mockStep2, 150);
        $builder->addBuildStep($this->mockStep1);

        $this->assertEquals(array('foo', 'bar'), $builder->build());
    }

    public function tearDown()
    {
        m::close();
    }
}
