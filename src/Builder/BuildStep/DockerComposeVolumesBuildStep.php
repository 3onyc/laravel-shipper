<?php

namespace x3tech\LaravelShipper\Builder\BuildStep;

use x3tech\LaravelShipper\DockerCompose\Container;
use x3tech\LaravelShipper\CompatBridge;

abstract class DockerComposeVolumesBuildStep implements DockerComposeBuildStepInterface
{
    /**
     * Add local volumes to container if current env is in config['mount_volumes']
     *
     * @param Container $container
     */
    protected function addVolumes(
        Container $container,
        CompatBridge $compat,
        \Illuminate\Foundation\Application $app
    ) {
        $env = $app->environment();
        $cfg = $compat->getShipperConfig();

        if (!in_array($env, $cfg['mount_volumes'])) {
            return;
        }

        array_walk($cfg['volumes'], function($host, $guest) use ($container) {
            $container->setVolume($host, $guest);
        });
    }
}
