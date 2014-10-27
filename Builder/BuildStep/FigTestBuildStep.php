<?php
namespace x3tech\LaravelShipper\Builder\BuildStep;

use Illuminate\Config\Repository;

/**
 * Add container that executes tests
 *
 * @see FigBuildStepInterface
 */
class FigTestBuildStep implements FigBuildStepInterface
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
        $cfg = $this->config->get('shipper::config');
        $env = $this->config->getEnvironment();

        $structure['tests'] = array(
            'build' => '.',
            'command' => $cfg['test_cmd'],
            'environment' => array(
                'APP_ENV' => $env
            ),
            'links' => array('queue')
        );

        if (isset($structure['db'])) {
            $structure['tests']['links'][] = 'db';
        }
        
        return $this->addVolumes($structure, 'tests', $this->config);
    }
}
