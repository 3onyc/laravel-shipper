<?php
namespace x3tech\LaravelShipper\Builder\BuildStep;

use Illuminate\Config\Repository;

/**
 * Add queue+worker containers definition to fig.yml for supported queue drivers
 *
 * @see FigBuildStepInterface
 */
class FigQueueBuildStep implements FigBuildStepInterface
{
    use FigVolumesTrait;

    /**
     * @var Illuminate\Config\Repository
     */
    protected $config;

    protected static $supported = array(
        'beanstalkd' => 'addBeanstalkd'
    );

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
        $conn = $this->getConnection();

        if (!array_key_exists($conn['driver'], self::$supported)) {
            return $structure;
        }

        if ($conn['driver'] !== 'sync') {
            $structure = $this->addWorker($structure, $conn);
        }

        $callback = array($this, self::$supported[$conn['driver']]);
        return call_user_func($callback, $structure, $conn);
    }

    /**
     * Get the configured default connection
     *
     * @return array
     */
    protected function getConnection()
    {
        $queueConfig = $this->config->get('queue');
        return $queueConfig['connections'][$queueConfig['default']];
    }

    /**
     * Add Beanstalkd container to the fig.yml structure
     *
     * @param array $structure
     * @param array $conn Queue connection config
     *
     * @return array
     */
    protected function addBeanstalkd(array $structure, array $conn)
    {
        $structure['app']['links'][] = 'queue';
        $structure['queue'] = array(
            'image' => 'kdihalas/beanstalkd',
        );

        return $structure;
    }

    protected function addWorker(array $structure, array $conn)
    {
        $env = $this->config->getEnvironment();
        $structure['worker'] = array(
            'build' => '.',
            'command' => '/var/www/artisan queue:listen',
            'environment' => array(
                'APP_ENV' => $env
            ),
            'links' => array('queue')
        );

        if (isset($structure['db'])) {
            $structure['worker']['links'][] = 'db';
        }

        return $this->addVolumes($structure, 'worker', $this->config);
    }
}
