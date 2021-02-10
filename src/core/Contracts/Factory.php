<?php

namespace Core\Contracts;

use Core\Application;

interface Factory
{
    public static function make(Application $app);
}