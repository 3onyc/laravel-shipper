<?php
namespace x3tech\LaravelShipper\Command;

use Illuminate\Console\Command;
use Illuminate\Config\Repository;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;

use x3tech\LaravelShipper\Service\DockerService;

class RunCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'docker:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Create and start the container for <env>";

    /**
     * @var DockerService
     */
    protected $docker;

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
        DockerService $docker,
        \Illuminate\Config\Repository $config
    ) {
        parent::__construct();

        $this->docker = $docker;
        $this->config = $config;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $env = $this->config->getEnvironment();
        $this->info(sprintf("Starting container for env '%s'...", $env));

        if (!$this->docker->hasImage($env)) {
            $this->error(sprintf(
                "No image for env '%s' please run 'artisan docker:build' first",
                $env
            ));
            return;
        }

        if ($this->docker->hasContainer($env)) {
            $this->error(sprintf(
                "Container for env '%s' already running, run 'artisan docker:stop' first",
                $env
            ));
            return;
        }

        $result = $this->docker->startContainer($env, function ($type, $buffer) {
            $method = $type == Process::ERR ? 'error' : 'info';
            call_user_func([$this, $method], $buffer);
        });

        if ($result) {
            $this->info(sprintf(
                "'%s' container started, you can now navigate to http://localhost:%u",
                $env,
                $this->config->get('shipper::config.port')
            )); 
        }
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
