<?php
namespace x3tech\LaravelShipper\Command;

use Illuminate\Console\Command;
use Illuminate\Config\Repository;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use x3tech\LaravelShipper\Service\DockerService;

class CleanCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'docker:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Delete images and running containers";

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
        $this->info(sprintf("Cleaning env '%s'...", $env));

        if ($this->docker->hasContainer($env)) {
            $this->info(sprintf("Deleting '%s' container...", $env));
            $this->docker->deleteContainer($env);
        }

        if ($this->docker->hasImage($env)) {
            $this->info(sprintf("Deleting '%s' image...", $env));
            $this->docker->deleteImage($env);
        }

        $this->info("Done...");
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
