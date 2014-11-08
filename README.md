# Laravel Shipper

Integrating Laravel, Docker and Fig

## Requirements

* [Docker](https://docker.com/)
* [Fig](http://www.fig.sh/)

## Instructions

1. Add to `composer.json`

   ```bash
   composer require 'x3tech/laravel-shipper' '~0.3'
   ```
2. Add the provider to `config/app.php`

   ```php
   'providers' => array(
       ...
       'x3tech\LaravelShipper\Provider\ShipperProvider'
   );
   ```

3. If using MySQL, set host to `db` in `database.php`
4. If using beanstalkd, set host to `queue` in `queue.php`
5. Modify the env detection in `bootstrap/start.php` as follows

   ```php
   $env = $app->detectEnvironment(function () {
       return getenv('APP_ENV') ?: 'production';
   });
   ```

   This allows for easier environment switching, just put `APP_ENV=<env>` in front
   of artisan calls to execute them for that environment.
6. Generate the `fig.yml` config file
   `APP_ENV=local ./artisan shipper:create:all`
7. Start the containers
   `fig up`
8. Wait until fig started the containers, and then visit http://localhost:8080

## FAQ

### How do I run a command on my project (Such as artisan)

`fig run app <command>`

**Examples**

Artisan:

`fig run app ./artisan`

PHPUnit:

`fig run app vendor/bin/phpunit'

