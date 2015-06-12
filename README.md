# Laravel Shipper

Integrating Laravel, Docker and Fig

## Requirements

* [Docker](https://docker.com/)
* [Docker-Compose](https://docs.docker.com/compose/)

## Instructions

1. Add to `composer.json`

   ```bash
   composer require 'x3tech/laravel-shipper' '>=0.5'
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
5. (Laravel 4.2 Only) Modify the env detection in `bootstrap/start.php` as follows

   ```php
   $env = $app->detectEnvironment(function () {
       return getenv('APP_ENV') ?: 'production';
   });
   ```

   This allows for easier environment switching, just put `APP_ENV=<env>` in front
   of artisan calls to execute them for that environment.
6. Generate the `docker-compose.yml` config file

   In Laravel 4.2

   `APP_ENV=local ./artisan shipper:create:all`

   In Laravel 5 (APP_ENV is set in .env)

   `./artisan shipper:create:all`

7. Start the containers
   `docker-compose up`
8. Wait until fig started the containers, and then visit http://localhost:8080

## FAQ

### How do I run a command on my project (Such as artisan)

`docker-compose run --rm app <command>`

**Examples**

Artisan:

`docker-compose run --rm app ./artisan`

PHPUnit:

`docker-compose run --rm app vendor/bin/phpunit'

