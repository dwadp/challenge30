<?php

namespace Core\Config;

use Core\Application;
use Core\Config\Config;
use Core\Contracts\Factory;

class ConfigFactory implements Factory
{
    public static function make(Application $app)
    {
        $configPath = $app->makePath('config');
        $options    = [
            'path'  => $configPath,
            'files' => [
                'app',
                'database',
                'view'
            ]
        ];

        return new Config($options);
    }
}