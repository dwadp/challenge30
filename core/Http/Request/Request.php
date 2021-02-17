<?php

namespace Core\Http\Request;

use Core\Http\Request\InputRequest;
use Core\Http\Request\QueryRequest;

class Request
{
    /**
     * The request collection instances
     *
     * @var array
     */
    protected $collections = [];

    public function __construct()
    {
        $this->collections = [
            'query' => new QueryRequest,
            'input' => new InputRequest,
        ];
    }
    
    /**
     * Capture all incoming input request
     *
     * @return void
     */
    public function capture()
    {
        foreach ($this->collections as $request) {
            $request->capture();
        }
    }

    /**
     * Merge all data from 'query' & 'input' request
     * If the query has the same field name with the input then it will be override 
     * by the input field
     *
     * @return array
     */
    public function all()
    {
        $data = [];

        foreach ($this->collections as $request) {
            $data = array_merge($data, $request->all());
        }

        return $data;
    }

    /**
     * Check if specific field has been captured from the request
     *
     * @param   string      $field
     * @return  boolean
     */
    public function has($field)
    {
        return array_key_exists($field, $this->all());
    }

    /**
     * Check if data is empty
     *
     * @return boolean
     */
    public function empty() {
        return count($this->all()) === 0;
    }

    /**
     * Retrieve previously captured request data
     *
     * @param   string      $field
     * @return  mixed|null
     */
    public function old($field)
    {
        if ($this->has($field)) {
            return $this->all()[$field];
        }
    }

    /**
     * Get request data only by the given fields
     *
     * @param   array       $fields
     * @return  array|null
     */
    public function only($fields = [])
    {
        if (count($fields) === 0) {
            return [];
        }

        return array_filter($this->all(), function($field) use ($fields) {
            return in_array($field, $fields);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Check if the specific request collection exists
     *
     * @param   string $name
     * @return  boolean
     */
    protected function exists($name)
    {
        return array_key_exists($name, $this->collections);
    }

    /**
     * Handle method call if exists in request collection then return the specific instance
     *
     * @param   string      $method
     * @param   mixed|null  $args
     * @return  null|object
     */
    public function __call($method, $args)
    {
        if ($this->exists($method)) {
            return $this->collections[$method];
        }
    }

    /**
     * Handle property getter if exists in the collection then return the specific instance
     *
     * @param   string      $name
     * @return  null|object
     */
    public function __get($name)
    {
        if ($this->exists($name)) {
            return $this->collections[$name];
        }
    }
}