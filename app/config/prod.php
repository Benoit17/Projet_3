<?php

// Doctrine (db)
$app['db.options'] = array(
    'driver'   => 'pdo_mysql',
    'charset'  => 'utf8',
    'host'     => 'localhost',
    'port'     => '3306',
    'dbname'   => 'projet_3',
    'user'     => 'projet_3_user',
    'password' => 'secret',
);

// define log level
$app['monolog.level'] = 'WARNING';