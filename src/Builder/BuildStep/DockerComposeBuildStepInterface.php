<?php
namespace x3tech\LaravelShipper\Builder\BuildStep;

use x3tech\LaravelShipper\DockerCompose\Definition;

interface DockerComposeBuildStepInterface
{
    /**
     * Run the BuildStep
     *
     * @param Definition $definition DockerCompose definition object
     */
    public function run(Definition $definition);
}
