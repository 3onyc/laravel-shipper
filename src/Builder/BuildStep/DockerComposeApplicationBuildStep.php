<?php
namespace x3tech\LaravelShipper\Builder\BuildStep;

use x3tech\LaravelShipper\DockerCompose\Definition;
use x3tech\LaravelShipper\DockerCompose\Container;
use x3tech\LaravelShipper\CompatBridge;

class DockerComposeApplicationBuildStep extends DockerComposeVolumesBuildStep
{
    /**
     * @var Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * @var x3tech\LaravelShipper\CompatBridge
     */
    protected $compat;

    public function __construct(
        CompatBridge $compat
    ) {
        $this->compat = $compat;
    }

    /**
     * {@inheritdoc}
     */
    public function run(Definition $definition)
    {
        $env = $this->compat->getEnvironment();
        $cfg = $this->compat->getShipperConfig();

        $app = new Container('app');
        $app->setBuild('.');
        $app->setPort($cfg['port'], 80);
        $app->setEnvironment(array(
            'APP_ENV' => $env
        ));
        $this->addVolumes($app, $this->compat);

        $definition->addContainer($app);
    }
}
