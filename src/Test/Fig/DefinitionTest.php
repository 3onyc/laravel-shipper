<?php
namespace x3tech\LaravelShipper\Test\Fig;

use PHPUnit_Framework_TestCase;

use x3tech\LaravelShipper\Fig\Container;
use x3tech\LaravelShipper\Fig\Definition;

class DefinitionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function testGetContainer()
    {
        $container = new Container('foo');

        $definition = new Definition();
        $definition->addContainer($container);

        $this->assertEquals($container, $definition->getContainer('foo'));
    }

    public function testToArray()
    {
        $container = new Container('foo');
        $container->setBuild('.');

        $definition = new Definition();
        $definition->addContainer($container);

        $this->assertArrayHasKey('foo', $definition->toArray());
    }
}
