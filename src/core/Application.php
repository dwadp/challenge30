<?php

namespace Core;

use Core\Config\ConfigFactory;
use Core\Contracts\Factory;
use Core\Database\Connection\ConnectionFactory;
use Core\Http\Request;
use Core\Registry;
use Core\Utility\Url;
use Core\Validator\Validator;
use Core\View\View;
use ReflectionClass;

class Application
{
    public static $instance;

    /**
     * Application base path
     *
     * @var string
     */
    protected static $basePath;

    /**
     * List of all core application dependencies
     *
     * @var array
     */
    protected $dependencies = [
        'config'        => ConfigFactory::class,
        'validator'     => Validator::class,
        'connection'    => ConnectionFactory::class,
        'request'       => Request::class,
        'url'           => Url::class,
        'view'          => View::class
    ];

    /**
     * Make the application instance
     *
     * @return Core\Application
     */
    public static function make($basePath)
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        self::$instance->initApplication($basePath);
        self::$instance->bootDependencies();
    }

    private function __construct() {}

    private function __clone() {}

    public function get($name)
    {
        return Registry::get($name);
    }

    public function register($name, $dependency)
    {
        Registry::register($name, $dependency);
    }

    public function setBasePath($basePath)
    {
        static::$basePath = $basePath;
    }

    public function getBasePath()
    {
        return static::$basePath;
    }

    public function makePath($path)
    {
        return $this->getBasePath() . '/' . $path;
    }

    private function initApplication($basePath)
    {
        self::$instance->setBasePath($basePath);

        Registry::register('app', self::$instance);
    }

    /**
     * Instantiate all application core dependencies
     *
     * @return void
     */
    public function bootDependencies()
    {
        foreach ($this->dependencies as $key => $dependency) {
            $class = new ReflectionClass($dependency);
            
            // If it's factory then it should run the factory
            if ($class->implementsInterface(Factory::class)) {
                $this->makeWithFactory($key, $dependency);
                continue;
            }

            // Register dependency with zero configuration / setup
            Registry::register($key, new $dependency(self::$instance));
        }
    }

    /**
     * Every class that implements 'Factory' should be instantiate using 'make' method
     *
     * @param string $key
     * @param Core\Contracts\Factory $class
     * @return void
     */
    protected function makeWithFactory($key, $class)
    {
        $instance = $class::make($this);

        Registry::register($key, $instance);
    }
}