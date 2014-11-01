<?php
return array(
    'maintainer'    => 'Foo <foo@acme.tld>',
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
