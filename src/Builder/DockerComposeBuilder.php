<?php
namespace x3tech\LaravelShipper\Builder;

use x3tech\LaravelShipper\Builder\BuildStep\DockerComposeBuildStepInterface;
use x3tech\LaravelShipper\DockerCompose\Definition;

class DockerComposeBuilder
{
    /**
     * @var array[int]array[]DockerComposeBuildStepInterface
     */
    protected $steps;

    public function __construct()
    {
        $this->steps = array();
    }

    public function build()
    {
        $definition = new Definition;

        foreach ($this->getPriorities() as $priority) {
            foreach ($this->steps[$priority] as $step) {
                $step->run($definition);
            }
        }

        return $definition->toArray();
    }

    protected function getPriorities()
    {
        $priorities = array_keys($this->steps);
        sort($priorities, SORT_NUMERIC);

        return $priorities;
    }

    public function addBuildStep(DockerComposeBuildStepInterface $step, $priority = 100)
    {
        $this->ensureArray($priority);
        $this->steps[$priority][] = $step;
    }

    private function ensureArray($priority)
    {
        if (!isset($this->steps[$priority])) {
            $this->steps[$priority] = array();
        }
    }
}
