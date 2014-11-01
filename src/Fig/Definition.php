<?php
namespace x3tech\LaravelShipper\Fig;

class Definition
{
    /**
     * @var []Container
     */
    protected $containers;

    public function __construct()
    {
        $this->containers = array();
    }

    public function addContainer(Container $container)
    {
        $this->containers[$container->getName()] = $container;
    }

    public function getContainer($name)
    {
        return isset($this->containers[$name]) ? $this->containers[$name] : null;
    }

    public function toArray()
    {
        $return = array();
        foreach ($this->containers as $container) {
            $return[$container->getName()] = $container->toArray();
        }

        return $return;
    }
}
