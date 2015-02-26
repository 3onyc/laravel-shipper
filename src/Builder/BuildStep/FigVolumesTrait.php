<?php

namespace x3tech\LaravelShipper\Builder\BuildStep;

use x3tech\LaravelShipper\Fig\Container;

trait FigVolumesTrait
{
    /**
     * Add local volumes to container if current env is in config['mount_volumes']
     *
     * @param Container $container
     */
    protected function addVolumes(
        Container $container,
        \Illuminate\Config\Repository $config,
        \Illuminate\Foundation\Application $app
    ) {
        $env = $app->environment();
        $cfg = $config->get('shipper');

        if (!in_array($env, $cfg['mount_volumes'])) {
            return;
        }

        array_walk($cfg['volumes'], function($host, $guest) use ($container) {
            $container->setVolume($host, $guest);
        });
    }
}
