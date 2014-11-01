<?php
namespace x3tech\LaravelShipper\Builder\BuildStep;

use x3tech\LaravelShipper\Fig\Definition;

interface FigBuildStepInterface
{
    /**
     * Run the BuildStep
     *
     * @param Definition $definition Fig definition object
     */
    public function run(Definition $definition);
}
