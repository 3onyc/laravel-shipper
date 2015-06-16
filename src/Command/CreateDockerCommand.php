<?php
namespace x3tech\LaravelShipper\Command;

use Illuminate\Console\Command;
use Illuminate\View\Factory;
use Illuminate\Config\Repository;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use x3tech\LaravelShipper\CompatBridge;

use RuntimeException;

class CreateDockerCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'shipper:create:docker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Create Dockerfile";

    /**
     * @var Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * @var x3tech\LaravelShipper\CompatBridge
     */
    protected $compat;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        \Illuminate\Foundation\Application $app,
        CompatBridge $compat
    ) {
        parent::__construct();

        $this->app = $app;
        $this->compat = $compat;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $cfg = $this->compat->getShipperConfig();
        $env = $this->app->environment();

        $this->info("Creating Dockerfile...");
        $this->createDockerFile($cfg, $env);
    }

    protected function createDockerFile(array $cfg, $env)
    {
        $view = 'shipper::Dockerfile_' . $env;

        $filePath = base_path() . '/Dockerfile';
        $fileContent = $this->compat->renderTemplate($view, $cfg);

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
