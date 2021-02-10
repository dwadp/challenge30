<?php

namespace Core\Http;

class Request
{
    /**
     * The request data
     *
     * @var array
     */
    private $data = [];
    
    /**
     * Capture all incoming request based on request method
     *
     * @return void
     */
    public function capture()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        switch ($requestMethod) {
            case 'GET':
                $this->captureGet();
                break;
            case 'POST':
                $this->capturePost();
                break;
        }
    }

    /**
     * Capture incoming 'GET' request
     *
     * @return void
     */
    private function captureGet()
    {
        $this->data = $_GET;
    }

    /**
     * Capture incoming 'POST' request
     *
     * @return void
     */
    private function capturePost()
    {
        $this->data = $_POST;
    }

    /**
     * Check if specific field has been captured from the request
     *
     * @param string $field
     * @return boolean
     */
    public function has($field)
    {
        if (!array_key_exists($field, $this->data)) {
            return false;
        }

        return true;
    }

    /**
     * Get specific request data by their field name
     *
     * @param string $field
     * @return mixed
     */
    public function get($field)
    {
        if (!$field) {
            return null;
        }

        if (!$this->has($field)) {
            return null;
        }

        return $this->data[$field];
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
     * @param array $fields
     * @return array|null
     */
    public function only($fields = [])
    {
        if (count($fields) === 0) {
            return [];
        }

        $filtered = array_filter($this->data, function($field) use ($fields) {
            return in_array($field, $fields);
        }, ARRAY_FILTER_USE_KEY);

        return $filtered;
    }

    public function empty() {
        return count($this->data) === 0;
    }
}