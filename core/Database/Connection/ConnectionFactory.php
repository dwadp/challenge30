<?php

namespace Core\Database\Connection;

use Core\Application;
use Core\Contracts\Factory;
use Core\Database\Connection\Connection;

class ConnectionFactory implements Factory
{
    /**
     * Make the connection instance
     *
     * @param   Core\Application $app
     * @return  Core\Database\Connection
     */
    public static function make(Application $app)
    {
        $config = $app->get('config');

        return Connection::make(
            $config->get('database.driver'),
            $config->get('database.host'),
            $config->get('database.name'),
            $config->get('database.username'),
            $config->get('database.password')
        );
    }
}