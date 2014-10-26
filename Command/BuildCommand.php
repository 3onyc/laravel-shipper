<?php
namespace x3tech\LaravelShipper\Command;

use Illuminate\Console\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\Process\Process;

use x3tech\LaravelShipper\Service\DockerService;

class BuildCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'docker:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Build docker image for <env>";

    /**
     * @var Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @var x3tech\LaravelShipper\Service\DockerService
     */
    protected $docker;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        \Illuminate\Config\Repository $config,
        DockerService $docker
    ) {
        parent::__construct();

        $this->config = $config;
        $this->docker = $docker;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $cfg = $this->config->get('shipper::config');
        $env = $this->argument('env');

        $this->createDockerFile($cfg, $env);
        $this->buildDockerImage($env);
    }

    protected function buildDockerImage($env)
    {
        $this->docker->buildImage($env, function ($type, $buffer) {
            $method = $type == Process::ERR ? 'error' : 'info';
            call_user_func([$this, $method], $buffer);
        });
    }

    protected function createDockerFile(array $cfg, $env)
    {
        $this->docker->createDockerFile($env);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array(
                'env',
                InputArgument::REQUIRED,
                "Environment to build 'prod' or 'dev'",
                null
            ),
        );
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
