<?php
return array(
    'maintainer'    => 'Foo <foo@acme.tld>',
    'type'          => 'hhvm',
    'php_image'     => 'x3tech/nginx-php:5.5',
    'hhvm_image'    => 'x3tech/nginx-hhvm:3.7',
    'port'          => 8080,
    'uid'           => 1000,
    'mount_volumes' => array('local'),
    'volumes'       => array(
        '/var/www' => '.',
        '/var/log/hhvm' => './app/storage/logs/hhvm',
        '/var/log/nginx' => './app/storage/logs/nginx'
    ),
    'test_cmd'      => 'vendor/bin/phpunit'
);
