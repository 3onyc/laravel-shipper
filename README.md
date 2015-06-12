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
5. Generate the `docker-compose.yml` config file

   `./artisan shipper:create:all`

7. Build and start the containers

   `docker-compose build && docker-compose up`

8. Wait until the containers are started, and visit http://localhost:8080

## FAQ

### How do I run a command on my project (Such as artisan)

`docker-compose run --rm app <command>`

**Examples**

Artisan:

`docker-compose run --rm app ./artisan`

PHPUnit:

`docker-compose run --rm app vendor/bin/phpunit'

