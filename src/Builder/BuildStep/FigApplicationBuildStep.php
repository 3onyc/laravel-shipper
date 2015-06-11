<?php
namespace x3tech\LaravelShipper\Builder\BuildStep;

use Illuminate\Config\Repository;

use x3tech\LaravelShipper\Fig\Definition;
use x3tech\LaravelShipper\Fig\Container;

class FigApplicationBuildStep extends FigVolumesBuildStep
{
    /**
     * @var Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * @var Illuminate\Config\Repository
     */
    protected $config;

    public function __construct(
        \Illuminate\Foundation\Application $app,
        \Illuminate\Config\Repository $config
    ) {
        $this->app = $app;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function run(Definition $definition)
    {
        $env = $this->app->environment();
        $cfg = $this->config->get('shipper');

        $app = new Container('app');
        $app->setBuild('.');
        $app->setPort($cfg['port'], 80);
        $app->setEnvironment(array(
            'APP_ENV' => $env
        ));
        $this->addVolumes($app, $this->config, $this->app);

        $definition->addContainer($app);
    }
}
