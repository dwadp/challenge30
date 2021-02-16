<?php

namespace Core\Config;

use Core\Application;
use Core\Config\Config;
use Core\Contracts\Factory;

class ConfigFactory implements Factory
{
    /**
     * Make the config instance
     *
     * @param Core\Application $app
     * @return Core\Config\Config
     */
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