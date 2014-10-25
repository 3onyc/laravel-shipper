<?php
namespace x3tech\LaravelShipper\Service;

use Symfony\Component\Process\Process;

use Illuminate\View\Factory;
use Illuminate\Config\Repository;

use RuntimeException;

class DockerService
{
    /**
     * @var Illuminate\View\Factory
     */
    protected $view;

    /**
     * @var array
     */
    protected $cfg;

    public function __construct(
        \Illuminate\View\Factory $view,
        \Illuminate\Config\Repository $config
    ) {
        $this->view = $view;
        $this->cfg = $config->get('shipper::config');
    }

    public function getName($env)
    {
        return sprintf("%s/%s-%s", $this->cfg['vendor'], $this->cfg['app'], $env);
    }

    public function buildImage($env, $logFunc = null)
    {
        $name = $this->getName($env);

        $proc = new Process(sprintf('docker build -t %s .', $name), base_path());
        $proc->setTimeout(null);
        $proc->run($logFunc);
    }

    public function hasImage($env)
    {
        $name = $this->getName($env);

        $proc = new Process(sprintf("docker inspect %s", $name));
        $proc->run();

        return $proc->getExitCode() === 0;
    }

    public function createDockerFile($env)
    {
        $view = 'shipper::Dockerfile_' . $env;

        $filePath = base_path() . '/Dockerfile';
        $fileContent = $this->view->make($view, $this->cfg)->render();

        if(file_put_contents($filePath, $fileContent) === false) {
            throw new RuntimeException(sprintf(
                "Failed to write Dockerfile, please check whether we have write permissions for '%s'",
                base_path()
            ));
        };
    }
}
