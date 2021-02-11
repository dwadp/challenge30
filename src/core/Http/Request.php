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
        $this->data = $this->sanitizeData($_GET);;
    }

    /**
     * Capture incoming 'POST' request
     *
     * @return void
     */
    private function capturePost()
    {
        $this->data = $this->sanitizeData($_POST);
    }

    /**
     * Sanitize all input data
     *
     * @param   array $data
     * @return  array
     */
    private function sanitizeData($data)
    {
        $result = [];

        foreach ($data as $key => $value) {
            $sanitized      = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            $result[$key]   = $sanitized;
        }

        return $result;
    }

    /**
     * Check if specific field has been captured from the request
     *
     * @param   string $field
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
     * Get specific request data by their field name
     *
     * @param   string $field
     * @return  mixed
     */
    public function get($field)
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
     * @param   array $fields
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