<?php
namespace x3tech\LaravelShipper\Test\Builder\BuildStep;

use PHPUnit_Framework_TestCase;
use x3tech\LaravelShipper\Fig\Definition;
use x3tech\LaravelShipper\Fig\Container;

class FigBuildStepTestBase  extends PHPUnit_Framework_TestCase
{
    protected function getDefinition()
    {
        $app = new Container('app');
        $app->setImage('foo/bar');
        $definition = new Definition;
        $definition->addContainer($app);

        return $definition;
    }
}
