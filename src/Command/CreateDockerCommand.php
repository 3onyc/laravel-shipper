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

        // Add newlines to work around PHP eating them
        // (See https://github.com/laravel/framework/issues/463)
        $cfgNewLine = $cfg;
        foreach ($cfgNewLine as $key => $value) {
            if (in_array($key, array('hhvm_image', 'php_image', 'maintainer'))) {
                $cfgNewLine[$key] = $value . "\n";
            }
        }

        $view = sprintf('shipper::Dockerfile_%s_%s', $cfg['type'], $env);

        $filePath = base_path() . '/Dockerfile';
        $fileContent = $this->compat->renderTemplate($view, $cfgNewLine);

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
