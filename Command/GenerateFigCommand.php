<?php
namespace x3tech\LaravelShipper\Command;

use Illuminate\Console\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Yaml\Yaml;

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
        \Illuminate\Config\Repository $config
    ) {
        parent::__construct();

        $this->config = $config;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $cfg = $this->config->get('shipper::config');
        $env = $this->config->getEnvironment();

        $this->info('Generating fig.yml...');
        $this->createFigYaml($cfg, $env);
    }

    protected function createFigYaml(array $cfg, $env)
    {
        $structure = array(
            'web' => array(
                'build' => '.',
                'ports' => array(
                    sprintf('%s:80', $cfg['port'])
                ),
                'environment' => array(
                    'APP_ENV' => $env
                ),
                'links' => array()
            )
        );

        $structure = $this->addDependencies($structure);

        if (in_array($env, $cfg['mount_volumes'])) {
            $structure['web']['volumes'] = array(
                '.:/var/www',
                './app/storage/logs/dev:/var/log'
            );
        }

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
