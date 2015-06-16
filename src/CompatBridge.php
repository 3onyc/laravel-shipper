<?php
namespace x3tech\LaravelShipper;

use Illuminate\Config\Repository;
use Illuminate\View\Compilers\BladeCompiler;

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
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * @var Illuminate\View\Factory|Illuminate\View\Environment
     *
     * Class depends on Laravel version (< 4.2 = Environment, 4.2+ = Factory)
     */
    protected $view;


    /**
     * @var Illuminate\View\Compilers\BladeCompiler
     */
    protected $blade;

    /**
     * @param string $laravelVersion
     * @param \Illuminate\Config\Repository $config
     */
    public function __construct(
        $laravelVersion,
        \Illuminate\Foundation\Application $app,
        \Illuminate\Config\Repository $config,
        $view,
        BladeCompiler $blade
    ) {
        $this->laravelVersion = $this->versionRemovePatchLevel($laravelVersion);
        $this->app = $app;
        $this->config = $config;
        $this->view = $view;
        $this->blade = $blade;
    }

    /**
     * Wrapper around Laravel's Blade template engine
     *
     * Wraps Blade to keep raw tags consistent across Laravel versions for
     * laravel-shipper
     *
     * @param string  $view     Name of the view to render
     * @param array   $context  Context to pass to the view
     */
    public function renderTemplate($view, $context = array())
    {
        if (!property_exists($this->blade, 'rawTags')) {
            $this->blade->setContentTags('{!!', '!!}');
        }

        $rendered = $this->view->make($view, $context)->render();

        if (!property_exists($this->blade, 'rawTags')) {
            $this->blade->setContentTags('{{{', '}}}');
        }

        return $rendered;
    }

    /**
     * Remove the patch level from an x.y.z version string
     *
     * For example:
     *  4.2.17 -> 4.2
     *  5.1.2  -> 5.1
     *
     * @param string $version Version to remove patch level from
     * @return string
     */
    protected function versionRemovePatchLevel($version)
    {
        $parts = explode('.', $version, 3);
        return $parts[0] . '.' . $parts[1];
    }

    public function getEnvironment()
    {
        return $this->app->environment();
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
