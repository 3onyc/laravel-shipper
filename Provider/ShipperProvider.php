<?php
namespace x3tech\LaravelShipper\Provider;

use Illuminate\Support\ServiceProvider;

use x3tech\LaravelShipper\Command\GenerateFigCommand;
use x3tech\LaravelShipper\Command\GenerateDockerCommand;
use x3tech\LaravelShipper\Command\GenerateAllCommand;

use x3tech\LaravelShipper\Builder\FigBuilder;

use x3tech\LaravelShipper\Builder\BuildStep\FigApplicationBuildStep;
use x3tech\LaravelShipper\Builder\BuildStep\FigDatabaseBuildStep;
use x3tech\LaravelShipper\Builder\BuildStep\FigQueueBuildStep;

class ShipperProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            'laravel_shipper.command.generate_fig',
            'x3tech\LaravelShipper\Command\GenerateFigCommand'
        );
        $this->app->bind(
            'laravel_shipper.command.generate_docker',
            'x3tech\LaravelShipper\Command\GenerateDockerCommand'
        );
        $this->app->bind(
            'laravel_shipper.command.generate_all',
            'x3tech\LaravelShipper\Command\GenerateAllCommand'
        );

        $this->app->bind(
            'laravel_shipper.fig_builder',
            'x3tech\LaravelShipper\Builder\FigBuilder'
        );
        $this->app->singleton(
            'x3tech\LaravelShipper\Builder\FigBuilder'
        );
    }

    public function boot()
    {
        $this->package('x3tech/laravel-shipper', 'shipper', dirname(__DIR__));

        $this->commands('laravel_shipper.command.generate_fig');
        $this->commands('laravel_shipper.command.generate_docker');
        $this->commands('laravel_shipper.command.generate_all');

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
