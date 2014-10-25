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
    protected $description = "Build development docker image";

    /**
     * @var Illuminate\View\Factory
     */
    protected $view;

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
        \Illuminate\View\Factory $view,
        \Illuminate\Config\Repository $config,
        DockerService $docker
    ) {
        parent::__construct();

        $this->view = $view;
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

        if ($env === 'dev' && !$this->productionImageBuilt($cfg)) {
            $this->info('Production image not found, building before dev');
            $this->call($this->name, array('env' => 'prod'));
        }

        $this->createDockerFile($cfg, $env);
        $this->buildDockerImage($cfg, $env);
    }

    protected function productionImageBuilt(array $cfg) {
        $imgName = sprintf('%s/%s-prod', $cfg['vendor'], $cfg['app']);
        return $this->docker->hasImage($imgName);
    }

    protected function buildDockerImage(array $cfg, $env)
    {
        $imgName = sprintf('%s/%s-%s', $cfg['vendor'], $cfg['app'], $env);
        $this->docker->buildImage($imgName, function ($type, $buffer) {
            $method = $type == Process::ERR ? 'error' : 'info';
            call_user_func([$this, $method], $buffer);
        });
    }

    protected function createDockerFile(array $cfg, $env)
    {
        $viewName = 'shipper::Dockerfile_' . $env;
        $dockerfilePath = base_path() . '/Dockerfile';

        $dockerfileContent = $this->view->make($viewName, $cfg)->render();

        if(file_put_contents($dockerfilePath, $dockerfileContent) === false) {
            throw new \Exception("Fail, TODO output");
        };
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
