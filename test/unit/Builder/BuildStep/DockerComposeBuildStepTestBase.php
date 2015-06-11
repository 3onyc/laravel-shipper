<?php
namespace x3tech\LaravelShipper\Test\Builder\BuildStep;

use PHPUnit_Framework_TestCase;
use x3tech\LaravelShipper\DockerCompose\Definition;
use x3tech\LaravelShipper\DockerCompose\Container;

class DockerComposeBuildStepTestBase  extends PHPUnit_Framework_TestCase
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
