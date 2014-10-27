<?php
namespace x3tech\LaravelShipper\Builder\BuildStep;

use Illuminate\Config\Repository;

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
    public function run(array $structure)
    {
        $env = $this->config->getEnvironment();
        $cfg = $this->config->get('shipper::config');

        $structure['app'] = array(
            'build' => '.',
            'ports' => array(
                sprintf('%s:80', $cfg['port'])
            ),
            'environment' => array(
                'APP_ENV' => $env
            ),
            'volumes' => array(),
            'links' => array()
        );

        return $this->addVolumes($structure, 'app', $this->config);
    }
}
