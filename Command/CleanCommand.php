<?php
namespace x3tech\LaravelShipper\Command;

use Illuminate\Console\Command;
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
        if ($this->docker->hasContainer('dev')) {
            $this->info("Deleting dev container...");
            $this->docker->deleteContainer('dev');
        }
        if ($this->docker->hasContainer('prod')) {
            $this->info("Deleting prod container...");
            $this->docker->deleteContainer('prod');
        }

        if ($this->docker->hasImage('dev')) {
            $this->info("Deleting dev image...");
            $this->docker->deleteImage('dev');
        }

        if ($this->docker->hasImage('prod')) {
            $this->info("Deleting prod image...");
            $this->docker->deleteImage('prod');
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
