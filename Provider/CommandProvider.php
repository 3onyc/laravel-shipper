<?php
namespace x3tech\LaravelShipper\Provider;

use Illuminate\Support\ServiceProvider;

use x3tech\LaravelShipper\Command\BuildCommand;
use x3tech\LaravelShipper\Command\OpenWebCommand;
use x3tech\LaravelShipper\Command\CleanCommand;
use x3tech\LaravelShipper\Command\RunCommand;

class CommandProvider extends ServiceProvider
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
    }

    public function boot()
    {
        $this->package('x3tech/laravel-shipper', 'shipper', dirname(__DIR__));

        $this->commands('laravel_shipper.command.generate_fig');
        $this->commands('laravel_shipper.command.generate_docker');
        $this->commands('laravel_shipper.command.generate_all');
    }
}
