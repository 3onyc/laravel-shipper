<?php
namespace x3tech\LaravelShipper\Provider;

use Illuminate\Support\ServiceProvider;

use x3tech\LaravelShipper\Command\BuildDevCommand;
use x3tech\LaravelShipper\Command\BuildProdCommand;
use x3tech\LaravelShipper\Command\OpenWebCommand;
use x3tech\LaravelShipper\Command\CleanCommand;
use x3tech\LaravelShipper\Command\RunCommand;

class CommandProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            'laravel_shipper.command.build_dev',
            'x3tech\LaravelShipper\Command\BuildDevCommand'
        );
        $this->app->bind(
            'laravel_shipper.command.build_prod',
            'x3tech\LaravelShipper\Command\BuildProdCommand'
        );
        $this->app->bind(
            'laravel_shipper.command.start',
            'x3tech\LaravelShipper\Command\StartCommand'
        );
        $this->app->bind(
            'laravel_shipper.command.stop',
            'x3tech\LaravelShipper\Command\StopCommand'
        );
        $this->app->bind(
            'laravel_shipper.command.clean',
            'x3tech\LaravelShipper\Command\CleanCommand'
        );
    }

    public function boot()
    {
        $this->commands('laravel_shipper.command.build_dev');
        $this->commands('laravel_shipper.command.build_prod');
        $this->commands('laravel_shipper.command.start');
        $this->commands('laravel_shipper.command.stop');
        $this->commands('laravel_shipper.command.clean');
    }
}
