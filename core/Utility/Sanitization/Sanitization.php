<?php

namespace Core\Utility\Sanitization;

use Core\Utility\Sanitization\HtmlSanitization;
use Exception;

class Sanitization
{
    /**
     * List of all sanitization handlers
     *
     * @var array
     */
    protected $sanitizations = [];

    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Initialize all sanitizations handlers
     *
     * @return void
     */
    protected function initialize()
    {
        $this->sanitizations = [
            'html' => new HtmlSanitization
        ];
    }

    /**
     * Check if sanitization handler exists
     *
     * @param   string $name
     * @return  boolean
     */
    protected function has($name)
    {
        return array_key_exists($name, $this->sanitizations);
    }

    /**
     * Handle any property access to a specific sanitization handler
     *
     * @param   string  $method
     * @param   mixed   $args
     * @return  mixed
     */
    public function __get($name)
    {
        if ($this->has($name)) {
            return $this->sanitizations[$name];
        }

        throw new Exception("Sanitization [{$name}] is not exists.");
    }

    /**
     * Handle any method call to a specific sanitization handler
     *
     * @param   string  $method
     * @param   mixed   $args
     * @return  mixed
     */
    public function __call($method, $args)
    {
        if ($this->has($method)) {
            return $this->sanitizations[$method];
        }

        throw new Exception("Sanitization [{$method}] is not exists.");
    }
}