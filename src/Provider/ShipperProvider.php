<?php
namespace x3tech\LaravelShipper\Provider;

use Illuminate\Support\ServiceProvider;

use x3tech\LaravelShipper\Command\CreateFigCommand;
use x3tech\LaravelShipper\Command\CreateDockerCommand;
use x3tech\LaravelShipper\Command\CreateDirsCommand;
use x3tech\LaravelShipper\Command\CreateAllCommand;

use x3tech\LaravelShipper\Builder\FigBuilder;

use x3tech\LaravelShipper\Builder\BuildStep\FigApplicationBuildStep;
use x3tech\LaravelShipper\Builder\BuildStep\FigDatabaseBuildStep;
use x3tech\LaravelShipper\Builder\BuildStep\FigQueueBuildStep;

class ShipperProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            'laravel_shipper.command.create_fig',
            'x3tech\LaravelShipper\Command\CreateFigCommand'
        );
        $this->app->bind(
            'laravel_shipper.command.create_docker',
            'x3tech\LaravelShipper\Command\CreateDockerCommand'
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

        $this->app->bind(
            'laravel_shipper.fig_builder',
            'x3tech\LaravelShipper\Builder\FigBuilder'
        );
        $this->app->singleton(
            'x3tech\LaravelShipper\Builder\FigBuilder'
        );

        $this->app->singleton(
            'x3tech\LaravelShipper\SupportReporter'
        );
    }

    public function boot()
    {
        $this->package('x3tech/laravel-shipper', 'shipper', LARAVEL_SHIPPER_ROOT);

        $this->commands('laravel_shipper.command.create_fig');
        $this->commands('laravel_shipper.command.create_docker');
        $this->commands('laravel_shipper.command.create_dirs');
        $this->commands('laravel_shipper.command.create_all');
        $this->commands('laravel_shipper.command.check');

        $this->initFigBuilder();
    }

    protected function initFigBuilder()
    {
        $builder = $this->app->make('laravel_shipper.fig_builder');
        $builder->addBuildStep($this->app->make(
            'x3tech\LaravelShipper\Builder\BuildStep\FigApplicationBuildStep'
        ), 25);
        $builder->addBuildStep($this->app->make(
            'x3tech\LaravelShipper\Builder\BuildStep\FigDatabaseBuildStep'
        ), 50);
        $builder->addBuildStep($this->app->make(
            'x3tech\LaravelShipper\Builder\BuildStep\FigQueueBuildStep'
        ), 50);
        $builder = $this->app->make('laravel_shipper.fig_builder');
    }
}
