<?php

use App\Core\Config;
use App\Core\Registry;

$configPath = __DIR__ . '/../config';
$options    = [
    'path'  => $configPath,
    'files' => [
        'app',
        'database',
        'view'
    ]
];
$config     = new Config($options);

Registry::register('config', $config);