<?php

namespace x3tech\LaravelShipper\Builder\BuildStep;

trait FigVolumesTrait
{
    /**
     * Add local volumes to fig.yml if current env is in config['mount_volumes']
     *
     * @param array $structure
     *
     * @return array
     */
    protected function addVolumes(
        array $structure,
        $container,
        \Illuminate\Config\Repository $config
    ) {
        $env = $config->getEnvironment();
        $cfg = $config->get('shipper::config');

        if (!in_array($env, $cfg['mount_volumes'])) {
            return $structure;
        }

        $structure[$container]['volumes'] = $cfg['volumes'];

        return $structure;
    }
}
