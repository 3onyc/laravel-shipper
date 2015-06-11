<?php
namespace x3tech\LaravelShipper\Test\Builder;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use x3tech\LaravelShipper\DockerCompose\Definition;
use x3tech\LaravelShipper\DockerCompose\Container;

use x3tech\LaravelShipper\Builder\DockerComposeBuilder;
use x3tech\LaravelShipper\Builder\BuildStep\DockerComposeBuildStepInterface;

class DockerComposeBuilderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $definition = 'x3tech\LaravelShipper\DockerCompose\Definition';
        $cls = 'x3tech\LaravelShipper\Builder\BuildStep\DockerComposeBuildStepInterface';
        $this->builder = new DockerComposeBuilder();

        $this->mockStep1 = m::mock($cls)
             ->shouldReceive('run')
             ->with(m::type($definition))
             ->andReturnUsing(function (Definition $definition) {
                 $container = new Container('foo');
                 $container->setImage('foo/bar');
                 $container->setPort(8000, 80);

                 $definition->addContainer($container);
             })
             ->getMock();

        $this->mockStep2 = m::mock($cls)
             ->shouldReceive('run')
             ->with(m::type($definition))
             ->andReturnUsing(function (Definition $definition) {
                 $definition->getContainer('foo')->setPort(8080, 80);
             })
             ->getMock();
    }
    public function testBuildOne()
    {
        $builder = new DockerComposeBuilder();
        $builder->addBuildStep($this->mockStep1);

        $this->assertArrayHasKey('foo', $builder->build());
    }

    public function testBuildPriorities()
    {
        $builder = new DockerComposeBuilder();
        $builder->addBuildStep($this->mockStep2, 150);
        $builder->addBuildStep($this->mockStep1);

        $result = $builder->build();
        $this->assertContains('8080:80', $result['foo']['ports']);
    }

    public function tearDown()
    {
        m::close();
    }
}
