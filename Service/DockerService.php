<?php
namespace x3tech\LaravelShipper\Service;

use Symfony\Component\Process\Process;

class DockerService
{
    public function buildImage($name, $logFunc = null)
    {
        $proc = new Process(sprintf('docker build -t %s .', $name), base_path());
        $proc->setTimeout(null);
        $proc->run($logFunc);
    }

    public function hasImage($name)
    {
        $proc = new Process("docker inspect " . $name);
        $proc->run();

        return $proc->getExitCode() === 0;
    }
}
