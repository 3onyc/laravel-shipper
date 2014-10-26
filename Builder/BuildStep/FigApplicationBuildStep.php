<?php
namespace x3tech\LaravelShipper\Builder\BuildStep;

use Illuminate\Config\Repository;

class FigApplicationBuildStep implements FigBuildStepInterface
{
    /**
     * @var string
     */
    protected $env;

    /**
     * @var array
     */
    protected $cfg;

    public function __construct(
        \Illuminate\Config\Repository $config
    ) {
        $this->env = $config->getEnvironment();
        $this->cfg = $config->get('shipper::config');
    }
    
    /**
     * {@inheritdoc}
     */
    public function run(array $structure)
    {
        $structure['app'] = array(
            'build' => '.',
            'ports' => array(
                sprintf('%s:80', $this->cfg['port'])
            ),
            'environment' => array(
                'APP_ENV' => $this->env
            ),
            'volumes' => array(),
            'links' => array()
        );

        return $this->addVolumes($structure);
    }

    /**
     * Add local volumes to fig.yml if current env is in config['mount_volumes']
     *
     * @param array $structure
     *
     * @return array
     */
    protected function addVolumes(array $structure)
    {
        if (!in_array($this->env, $this->cfg['mount_volumes'])) {
            return $structure;
        }

        $structure['app']['volumes'] = array(
            '.:/var/www',
            './app/storage/logs/hhvm:/var/log/hhvm',
            './app/storage/logs/nginx:/var/log/nginx'
        );

        return $structure;
    }
}
