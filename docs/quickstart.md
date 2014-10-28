# Quickstart

1. `composer require 'x3tech/laravel-shipper' '~0.1'`
2. Add `x3tech\LaravelShipper\Provider\ShipperProvider` to providers array in `config/app.php`
3. When using MySQL, modify `database.php` so that the database host is set to `db`
4. When using beanstalkd, modify `queue.php` so that the database host is set to `queue`
5. [OPTIONAL] Modify `bootstrap/start.php` so that the env detection is as follows

   ```php
   $env = $app->detectEnvironment(function () { 
       return getenv('APP_ENV') ?: 'production';
   });                                          
   ```
   
   This allows for easier environment switching, just put `APP_ENV=<env>` in front
   of artisan calls to execute them for that environment.
