<?php
namespace x3tech\LaravelShipper;

use Illuminate\Config\Repository;

/**
 * Class: CompatBridge
 *
 * Compatibility bridge to present a single interface across Laravel versions
 */
class CompatBridge
{
    /**
     * Current Laravel version
     *
     * @var string
     */
    protected $laravelVersion;

    /**
     * Laravel configuration
     *
     * @var ?!?
     */
    protected $config;

    /**
     * @param string $laravelVersion
     * @param \Illuminate\Config\Repository $config
     */
    public function __construct(
        $laravelVersion,
        \Illuminate\Config\Repository $config
    ) {
        $this->laravelVersion = $this->versionRemovePatchLevel($laravelVersion);
        $this->config = $config;
    }

    protected function versionRemovePatchLevel($version)
    {
        $parts = explode('.', $version, 3);
        return $parts[0] . '.' . $parts[1];
    }

    /**
     * Return Shipper config array
     *
     * @return array
     */
    public function getShipperConfig()
    {
        return $this->getConfig($this->getConfigSection());
    }

    /**
     * Pass call through to Laravel's Config::get
     *
     * @param mixed $key
     */
    public function getConfig($key, $default = null)
    {
        return $this->config->get($key, $default);
    }

    /**
     * Get the name of the LaravelShipper config section
     *
     * @return string
     */
    protected function getConfigSection()
    {
        switch ($this->laravelVersion) {
            case "4.0":
            case "4.1":
            case "4.2":
                return 'shipper::config';
                break;
            case "5.0":
            case "5.1":
                return 'shipper';
                break;
        }
    }
}
