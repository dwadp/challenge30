<?php

use App\Core\Config;
use App\Core\Database\Connection;
use App\Core\Registry;

$config     = Registry::get('config');
$connection = Connection::make(
    $config->get('database.driver'),
    $config->get('database.host'),
    $config->get('database.name'),
    $config->get('database.username'),
    $config->get('database.password'),
);

Registry::register('connection', $connection);