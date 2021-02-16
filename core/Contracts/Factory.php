<?php

namespace Core\Contracts;

use Core\Application;

interface Factory
{
    /**
     * Define of how a dependencies should be made
     *
     * @param   Core\Application $app
     * @return  object
     */
    public static function make(Application $app);
}