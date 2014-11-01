<?php
namespace x3tech\LaravelShipper\Builder\BuildStep;

use Illuminate\Config\Repository;

use x3tech\LaravelShipper\Fig\Definition;
use x3tech\LaravelShipper\Fig\Container;

class FigApplicationBuildStep implements FigBuildStepInterface
{
    use FigVolumesTrait;

    /**
     * @var Illuminate\Config\Repository
     */
    protected $config;

    public function __construct(
        \Illuminate\Config\Repository $config
    ) {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function run(Definition $definition)
    {
        $env = $this->config->getEnvironment();
        $cfg = $this->config->get('shipper::config');

        $app = new Container('app');
        $app->setBuild('.');
        $app->setPort($cfg['port'], 80);
        $app->setEnvironment(array(
            'APP_ENV' => $env
        ));
        $this->addVolumes($app, $this->config);

        $definition->addContainer($app);
    }
}
