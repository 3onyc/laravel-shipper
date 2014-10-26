<?php
namespace x3tech\LaravelShipper\Service;

use Symfony\Component\Process\ProcessBuilder;
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

    /**
     * Return name of the image for $env
     *
     * @param string $env
     *
     * @return string
     */
    public function getImageName($env)
    {
        return sprintf("%s/%s-%s", $this->cfg['vendor'], $this->cfg['app'], $env);
    }

    /**
     * Build a Docker image for $env
     *
     * @param string   $env
     * @param callable $logFunc Function to handle process output
     *                          signature: func($type, $buffer)
     */
    public function buildImage($env, $logFunc = null)
    {
        $name = $this->getImageName($env);

        $proc = new Process(sprintf('docker build -t %s .', $name), base_path());
        $proc->setTimeout(null);
        $proc->run($logFunc);
    }

    /**
     * @param string $env
     *
     * @return bool Whether docker image exists
     */
    public function hasImage($env)
    {
        $name = $this->getImageName($env);

        $proc = new Process(sprintf("docker inspect %s", $name));
        $proc->disableOutput();
        $proc->run();

        return $proc->getExitCode() === 0;
    }

    /**
     * @param string $env
     *
     * @throws RuntimeException When image deletion fails.
     */
    public function deleteImage($env)
    {
        $name = $this->getImageName($env, $force = true);

        $proc = new Process(sprintf("docker rmi %s", $name));
        $proc->run();

        if ($proc->getExitCode() !== 0) {
            throw new RuntimeException(
                "Failed to delete image, stderr: " . $proc->getErrorOutput()
            );
        }
    }

    /**
     * Render a Dockerfile from the blade template for $env.
     *
     * @param string $env
     *
     * @throws RuntimeException When writing to the Dockerfile fails
     */
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

    /**
     * Return name of the container for $env
     *
     * @param string $env
     *
     * @return string
     */
    public function getContainerName($env)
    {
        return sprintf("%s_%s-%s", $this->cfg['vendor'], $this->cfg['app'], $env);
    }

    /**
     * @param string   $env
     * @param callable $logFunc Function to handle process output
     *                          signature: func($type, $buffer)
     *
     * @return bool Whether the process exited succesfully
     */
    public function startContainer($env, $logFunc = null)
    {
        $process = (new ProcessBuilder())
            ->setPrefix("docker")
            ->setArguments($this->getStartArgs($env))
            ->getProcess();

        if ($env === "dev") {
            $this->ensureLogDirs();
        }

        $process->run($logFunc);

        return $process->getExitCode() === 0;
    }

    /**
     * Ensure that the log directories for hhvm and nginx are created.
     *
     * Only used in 'dev'
     */
    protected function ensureLogDirs()
    {
        $logBase = sprintf("%s/logs/dev", storage_path());
        if (!is_dir($logBase)) {
            mkdir($logBase, 0755);
        }
        
        foreach (array("hhvm", "nginx") as $dir) {
            $dirPath = sprintf("%s/%s", $logBase, $dir);
            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0755);
            }
        }
    }
    
    /**
     * Returns the arguments for the 'docker' call startContainer
     *
     * @param string $env
     *
     * @return array
     */
    protected function getStartArgs($env)
    {
        $image = $this->getImageName($env);
        $name = $this->getContainerName($env);

        $args = array(
            "run",
            sprintf("--publish=%u:80", $this->cfg['port']),
            sprintf("--name=%s", $name),
            sprintf("--env=APP_ENV=%s", $env),
            "--detach"
        );
        if ($env === "dev") {
            $args = array_merge($args, array(
                sprintf("--volume=%s:/var/www", base_path()),
                sprintf("--volume=%s/app/storage/logs/dev:/var/log", base_path()),
            ));
        }
        return array_merge($args, array($image));
    }

    /**
     * @param string $env
     *
     * @return bool Whether Docker container exists
     */
    public function hasContainer($env)
    {
        $name = $this->getContainerName($env);

        $proc = new Process(sprintf("docker inspect %s", $name));
        $proc->disableOutput();
        $proc->run();

        return $proc->getExitCode() === 0;
    }

    /**
     * @param string $env
     * @param bool   $force Force container deletion (Stop+delete when running)
     */
    public function deleteContainer($env, $force = true)
    {
        $name = $this->getContainerName($env);
        $flags = $force ? '-f' : '';

        $proc = new Process(sprintf("docker rm %s %s", $flags, $name));
        $proc->run();

        if ($proc->getExitCode() !== 0) {
            throw new RuntimeException(
                "Failed to delete container, stderr: " . $proc->getErrorOutput()
            );
        }
    }
}
