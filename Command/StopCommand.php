<?php
namespace x3tech\LaravelShipper\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use x3tech\LaravelShipper\Service\DockerService;

class StopCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'docker:stop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Stop and delete the container for <env>";

    /**
     * @var DockerService
     */
    protected $docker;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        DockerService $docker
    ) {
        parent::__construct();

        $this->docker = $docker;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $env = $this->argument('env');
        $this->info(sprintf("Deleting container for env '%s'...", $env));

        if (!$this->docker->hasContainer($env)) {
            $this->error(sprintf("No container running for env '%s'", $env));
            return;
        }

        $this->docker->deleteContainer($env);
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
