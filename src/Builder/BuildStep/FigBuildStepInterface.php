<?php
namespace x3tech\LaravelShipper\Builder\BuildStep;

interface FigBuildStepInterface
{
    /**
     * Run the BuildStep
     *
     * @param array $structure Current structure for the fig.yml file
     *
     * @return array The modified fig.yml structure
     */
    public function run(array $structure);
}
