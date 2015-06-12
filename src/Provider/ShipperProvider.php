<?php
namespace x3tech\LaravelShipper\Provider;

use Illuminate\Support\ServiceProvider;
use Illuminate\Container\BindingResolutionException;
use Illuminate\Foundation\Application;

use x3tech\LaravelShipper\Command\CheckCommand;
use x3tech\LaravelShipper\Command\CreateDockerComposeCommand;
use x3tech\LaravelShipper\Command\CreateDockerCommand;
use x3tech\LaravelShipper\Command\CreateDirsCommand;
use x3tech\LaravelShipper\Command\CreateAllCommand;

use x3tech\LaravelShipper\Builder\DockerComposeBuilder;
use x3tech\LaravelShipper\Builder\BuildStep\DockerComposeApplicationBuildStep;
use x3tech\LaravelShipper\Builder\BuildStep\DockerComposeDatabaseBuildStep;
use x3tech\LaravelShipper\Builder\BuildStep\DockerComposeQueueBuildStep;

use x3tech\LaravelShipper\CompatBridge;

class ShipperProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            'laravel_shipper.compat_bridge',
            'x3tech\LaravelShipper\CompatBridge'
        );
        $this->app->singleton('x3tech\LaravelShipper\CompatBridge', function($app) {
            return new CompatBridge(Application::VERSION, $app['config']);
        });

        $this->app->bind('laravel_shipper.command.create_docker', function($app) {
            return new CreateDockerCommand(
                $app,
                $app['laravel_shipper.compat_bridge'],
                $app['view']
            );
        });
        $this->app->bind(
            'laravel_shipper.command.create_docker_compose',
            'x3tech\LaravelShipper\Command\CreateDockerComposeCommand'
        );
        $this->app->bind(
            'laravel_shipper.command.create_dirs',
            'x3tech\LaravelShipper\Command\CreateDirsCommand'
        );
        $this->app->bind(
            'laravel_shipper.command.create_all',
            'x3tech\LaravelShipper\Command\CreateAllCommand'
        );
        $this->app->bind(
            'laravel_shipper.command.check',
            'x3tech\LaravelShipper\Command\CheckCommand'
        );

        $this->app->singleton(
            'x3tech\LaravelShipper\Builder\DockerComposeBuilder'
        );
        $this->app->bind(
            'laravel_shipper.docker_compose_builder',
            'x3tech\LaravelShipper\Builder\DockerComposeBuilder'
        );

        $this->app->singleton(
            'x3tech\LaravelShipper\SupportReporter'
        );
        $this->app->bind(
            'laravel_shipper.support_reporter',
            'x3tech\LaravelShipper\SupportReporter'
        );
    }

    private function boot4()
    {
        // Hack for 4.0
        try {
            $this->app['Illuminate\Config\Repository'];
        } catch (BindingResolutionException $e) {
            $this->app->bind('Illuminate\Config\Repository', function ($app) {
                return $app['config'];
            });
        }
        $this->package('x3tech/laravel-shipper', 'shipper', LARAVEL_SHIPPER_ROOT);
    }

    private function boot5()
    {
        $this->loadViewsFrom(LARAVEL_SHIPPER_ROOT . '/views', 'shipper');
        $this->mergeConfigFrom(LARAVEL_SHIPPER_ROOT . '/config/config.php', 'shipper');

        $this->publishes(array(
            LARAVEL_SHIPPER_ROOT . '/views' => base_path('resources/views/vendor/shipper')
        ), 'views');

        $this->publishes(array(
            LARAVEL_SHIPPER_ROOT . '/config/config.php' => config_path('shipper.php')
        ), 'config');
    }

    public function boot()
    {
        method_exists($this, 'package') ? $this->boot4() : $this->boot5();

        $this->commands('laravel_shipper.command.create_docker_compose');
        $this->commands('laravel_shipper.command.create_docker');
        $this->commands('laravel_shipper.command.create_dirs');
        $this->commands('laravel_shipper.command.create_all');
        $this->commands('laravel_shipper.command.check');

        $this->initDockerComposeBuilder();
    }

    protected function initDockerComposeBuilder()
    {
        $builder = $this->app->make('laravel_shipper.docker_compose_builder');
        $builder->addBuildStep($this->app->make(
            'x3tech\LaravelShipper\Builder\BuildStep\DockerComposeApplicationBuildStep'
        ), 25);
        $builder->addBuildStep($this->app->make(
            'x3tech\LaravelShipper\Builder\BuildStep\DockerComposeDatabaseBuildStep'
        ), 50);
        $builder->addBuildStep($this->app->make(
            'x3tech\LaravelShipper\Builder\BuildStep\DockerComposeQueueBuildStep'
        ), 50);
        $builder = $this->app->make('laravel_shipper.docker_compose_builder');
    }
}
