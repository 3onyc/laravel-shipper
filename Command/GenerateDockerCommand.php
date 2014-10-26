<?php
namespace x3tech\LaravelShipper\Command;

use Illuminate\Console\Command;
use Illuminate\View\Factory;
use Illuminate\Config\Repository;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use RuntimeException;

class GenerateDockerCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'shipper:generate:docker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Generate Dockerfile";

    /**
     * @var Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @var Illuminate\View\Factory
     */
    protected $view;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        \Illuminate\Config\Repository $config,
        \Illuminate\View\Factory $view
    ) {
        parent::__construct();

        $this->config = $config;
        $this->view = $view;
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

        $this->info("Generating Dockerfile...");
        $this->createDockerFile($cfg, $env);
    }

    protected function createDockerFile(array $cfg, $env)
    {
        $view = 'shipper::Dockerfile_' . $env;

        $filePath = base_path() . '/Dockerfile';
        $fileContent = $this->view->make($view, $cfg)->render();

        if(file_put_contents($filePath, $fileContent) === false) {
            throw new RuntimeException(sprintf(
                "Failed to write Dockerfile, please check whether we have write permissions for '%s'",
                base_path()
            ));
        };
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
