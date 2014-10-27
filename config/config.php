<?php
return array(
    'maintainer'    => 'Foo <foo@acme.tld>',
    'port'          => 8080,
    'uid'           => 1000,
    'mount_volumes' => array('local'),
    'volumes'       => array(
        '.:/var/www',
        './app/storage/logs/hhvm:/var/log/hhvm',
        './app/storage/logs/nginx:/var/log/nginx'
    ),
    'test_cmd'      => 'vendor/bin/phpunit'
);
