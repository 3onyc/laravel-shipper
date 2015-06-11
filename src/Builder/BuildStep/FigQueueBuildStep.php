<?php
namespace x3tech\LaravelShipper\Builder\BuildStep;

use Illuminate\Config\Repository;

use x3tech\LaravelShipper\SupportReporter;
use x3tech\LaravelShipper\Fig\Definition;
use x3tech\LaravelShipper\Fig\Container;

/**
 * Add queue+worker containers definition to fig.yml for supported queue drivers
 *
 * @see FigBuildStepInterface
 */
class FigQueueBuildStep extends FigVolumesBuildStep
{
    /**
     * @var Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @var Illuminate\Foundation\Application
     */
    protected $app;

    protected static $supported = array(
        'beanstalkd' => 'addBeanstalkd'
    );

    public function __construct(
        \Illuminate\Foundation\Application $app,
        \Illuminate\Config\Repository $config,
        SupportReporter $supportReporter
    ) {
        $this->app = $app;
        $this->config = $config;

        array_map(
            array($supportReporter, 'addSupportedQueue'),
            array_keys(static::$supported)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function run(Definition $definition)
    {
        $conn = $this->getConnection();
        if (!$this->isSupported($conn)) {
            return;
        }

        $queue = $this->getQueueContainer($conn);
        $definition->addContainer($queue);
        $definition->getContainer('app')->addLink($queue);

        if ($conn['driver'] !== 'sync') {
            $this->addWorker($definition, $conn);
        }
    }

    /**
     * @param array $conn
     *
     * @return Container
     */
    protected function getQueueContainer(array $conn)
    {
        $method = self::$supported[$conn['driver']];
        return $this->$method($conn);
    }

    /**
     * Returns whether the queue driver is supported
     *
     * @param array $conn
     *
     * @return bool
     */
    protected function isSupported(array $conn)
    {
        return array_key_exists($conn['driver'], self::$supported);
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
     * @param array $conn Queue connection config
     */
    protected function addBeanstalkd(array $conn)
    {
        $queue = new Container('queue');
        $queue->setImage('kdihalas/beanstalkd');

        return $queue;
    }

    /**
     * Add a queue worker to the definition
     *
     * @param Definition $definition
     * @param array $conn
     */
    protected function addWorker(Definition $definition, array $conn)
    {
        $env = $this->app->environment();

        $worker = new Container('worker');
        $worker->setBuild('.');
        $worker->setCommand(array('/var/www/artisan', 'queue:listen'));
        $worker->setEnvironment(array(
            'APP_ENV' => $env
        ));
        $worker->addLink($definition->getContainer('queue'));

        if ($definition->getContainer('db')) {
            $worker->addLink($definition->getContainer('db'));
        }

        $this->addVolumes($worker, $this->config, $this->app);
        $definition->addContainer($worker);
    }
}
