<?php
namespace x3tech\LaravelShipper\Command;

use Illuminate\Console\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Yaml\Yaml;

use x3tech\LaravelShipper\Builder\FigBuilder;

class GenerateFigCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'shipper:generate:fig';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Generate fig.yml";

    /**
     * @var Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        FigBuilder $figBuilder
    ) {
        parent::__construct();

        $this->figBuilder = $figBuilder;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->info('Generating fig.yml...');
        $this->createFigYaml();
    }

    protected function createFigYaml()
    {
        $structure = $this->figBuilder->build();

        $figPath = sprintf('%s/fig.yml', base_path());
        $figContents = Yaml::dump($structure, 3);

        if (file_put_contents($figPath, $figContents) === false) {
            throw new RuntimeException(sprintf(
                "Failed to write fig.yml, please check whether you have write permissions for '%s'",
                base_path()
            ));
        }
    }

    protected function addDependencies(array $structure)
    {
        $structure = $this->addDatabase($structure);
        $structure = $this->addQueue($structure);

        return $structure;
    }

    protected function addDatabase(array $structure)
    {
        $default = $this->config->get('database.default');
        $connections = $this->config->get('database.connections');
        $connection = $connections[$default];

        switch ($connection['driver']) {
            case 'mysql':
                $this->info("Adding MySQL dependency...");
                $structure['db'] = array(
                    'image' => 'x3tech/mysql',
                    'environment' => array(
                        'MYSQL_ROOT_PASSWORD' => $connection['password'],
                        'MYSQL_DATABASE' => $connection['database'],
                        'MYSQL_USER' => $connection['username'],
                        'MYSQL_PASSWORD' => $connection['password']
                    )
                );
                $structure['web']['links'][] = 'db';
                break;
            case 'pgsql':
                $this->info("Adding PostgreSQL dependency...");
                $this->error("NOTE: PostgreSQL is currently unsupported by HHVM.");

                $structure['db'] = array(
                    'image' => 'orchardup/postgresql',
                    'environment' => array(
                        'POSTGRESQL_DB' => $connection['database'],
                        'POSTGRESQL_USER' => $connection['username'],
                        'POSTGRESQL_PASS' => $connection['password']
                    )
                );
                $structure['web']['links'][] = 'db';
                break;
        }

        return $structure;
    }

    protected function addQueue(array $structure)
    {
        $env = $this->config->getEnvironment();
        $cfg = $this->config->get('shipper::config');
        $default = $this->config->get('queue.default');
        $connections = $this->config->get('queue.connections');
        $connection = $connections[$default];

        switch ($connection['driver']) {
            case 'beanstalkd':
                $this->info("Adding beanstalkd dependency...");
                $structure['web']['links'][] = 'queue';
                $structure['queue'] = array(
                    'image' => 'kdihalas/beanstalkd',
                );
                break;
        }

        if ($connection['driver'] !== 'sync') {
            $this->info("Adding queue worker...");
            $structure['worker'] = array(
                'build' => '.',
                'command' => '/var/www/artisan queue:listen',
                'environment' => array(
                    'APP_ENV' => $env
                ),
                'links' => array('queue'),

            );

            if (in_array($env, $cfg['mount_volumes'])) {
                $structure['worker']['volumes'] = array(
                    '.:/var/www',
                    './app/storage/logs/hhvm:/var/log/hhvm',
                    './app/storage/logs/nginx:/var/log/nginx'
                );
            }
        }

        return $structure;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array();
    }

}
