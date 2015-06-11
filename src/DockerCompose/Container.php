<?php
namespace x3tech\LaravelShipper\DockerCompose;

use InvalidArgumentException;

class Container
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $build;

    /**
     * @var string
     */
    protected $command;

    /**
     * @var string
     */
    protected $image;

    /**
     * @var string
     */
    protected $entrypoint;

    /**
     * @var []Container
     */
    protected $links;

    /**
     * @var [string]string
     */
    protected $environment;

    /**
     * @var [string]string
     */
    protected $volumes;

    /**
     * @var [int]int
     */
    protected $ports;

    public function __construct($name)
    {
        $this->name = $name;

        $this->links = array();
        $this->environment = array();
        $this->volumes = array();
        $this->ports = array();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $path
     */
    public function setBuild($path)
    {
        if (isset($this->image)) {
            throw new InvalidArgumentException(
                'Can only have one of image/build, image already set'
            );
        }

        $this->build = $path;
    }

    /**
     * @param string $image
     */
    public function setImage($image)
    {
        if (isset($this->build)) {
            throw new InvalidArgumentException(
                'Can only have one of image/build, build already set'
            );
        }

        $this->image = $image;
    }

    /**
     * @param string $entrypoint
     */
    public function setEntrypoint($entrypoint)
    {
        $this->entrypoint = $entrypoint;
    }

    /**
     * @param string $command
     */
    public function setCommand(array $command)
    {
        $this->command = $command;
    }

    /**
     * @param Container $container
     */
    public function addLink(Container $container)
    {
        $this->links[$container->getName()] = $container;
    }

    /**
     * @param [string]string $vars
     */
    public function setEnvironment(array $vars)
    {
        foreach ($vars as $key => $value) {
            $this->environment[$key] = $value;
        }
    }

    /**
     * @param string $host path on host
     * @param string $guest path on guest
     */
    public function setVolume($host, $guest)
    {
        $this->volumes[$host] = $guest;
    }

    public function setPort($host, $guest)
    {
        $this->ports[$host] = $guest;
    }

    public function toArray()
    {
        if ($this->build === null && $this->image === null) {
            throw new InvalidArgumentException('Need to have one of image/build set');
        }

        $return = array();
        if (count($this->links) > 0) {
            $return['links'] = $this->flattenLinks();
        }
        if (count($this->environment) > 0) {
            $return['environment'] = $this->environment;
        }
        if (count($this->volumes) > 0) {
            $return['volumes'] = $this->flattenKeyValue($this->volumes);
        }
        if (count($this->ports) > 0) {
            $return['ports'] = $this->flattenKeyValue($this->ports, '%u:%u');
        }
        if ($this->build !== null) {
            $return['build'] = $this->build;
        }
        if ($this->image !== null) {
            $return['image'] = $this->image;
        }
        if ($this->command !== null) {
            $return['command'] = $this->command;
        }
        if ($this->entrypoint !== null) {
            $return['entrypoint'] = $this->entrypoint;
        }

        return $return;
    }

    protected function flattenLinks()
    {
        return array_keys($this->links);
    }

    protected function flattenKeyValue(array $toFlatten, $format = '%s:%s')
    {
        return array_map(function ($value, $key) use ($format) {
            return sprintf($format, $key, $value);
        }, array_values($toFlatten), array_keys($toFlatten));
    }
}
