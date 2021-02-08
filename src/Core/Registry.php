<?php

namespace App\Core;

class Registry
{
    /**
     * All application dependencies
     *
     * @var array
     */
    private static $dependencies = [];

    /**
     * Register a new dependency
     *
     * @param string $name
     * @param object $object
     * @return void
     */
    public static function register($name, $object)
    {
        self::$dependencies[$name] = $object;
    }

    /**
     * Get registered dependency
     *
     * @param string $name
     * @return null|object
     */
    public static function get($name)
    {
        if (!array_key_exists($name, self::$dependencies)) {
            return null;
        }

        return self::$dependencies[$name];
    }
}