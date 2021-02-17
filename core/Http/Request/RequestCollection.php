<?php

namespace Core\Http\Request;

class RequestCollection
{
    /**
     * The request data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Append new request data to existing data
     *
     * @param array $data
     * @return void
     */
    protected function append($data)
    {
        $this->data = array_merge($this->data, $data);
    }

    /**
     * Check if specific field has been captured from the request
     *
     * @param   string      $field
     * @return  boolean
     */
    public function has($field)
    {
        return array_key_exists($field, $this->data);
    }

    /**
     * Check if data is empty
     *
     * @return boolean
     */
    public function empty() {
        return count($this->data) === 0;
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
            return $this->data[$field];
        }
    }

    /**
     * Get all captured request data
     *
     * @return array|null
     */
    public function all()
    {
        return $this->data;
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

        return array_filter($this->data, function($field) use ($fields) {
            return in_array($field, $fields);
        }, ARRAY_FILTER_USE_KEY);
    }
}