<?php

namespace Core\Validator;

class ValidationError
{
    /**
     * The error messages
     *
     * @var array
     */
    private $errors = [];

    /**
     * Set error message
     *
     * @param   string $key
     * @param   string $value
     * @return  void
     */
    public function set($field, $message)
    {
        $this->errors[$field][] = $message;
    }

    /**
     * Check if there's no error recorded
     *
     * @return boolean
     */
    public function empty()
    {
        return count($this->errors) === 0;
    }

    /**
     * Check if error exists by specific field/key
     *
     * @param   string $key
     * @return  boolean
     */
    public function has($field)
    {
        return isset($this->errors[$field]);
    }

    /**
     * Get the first error message captured by a specific field/key
     *
     * @param   string $key
     * @return  null|string
     */
    public function get($field)
    {
        if (($this->has($field)) && 
            (count($this->errors[$field]) > 0)) {
            return $this->errors[$field][0];
        }
    }

    /**
     * Get all validation error messages
     *
     * @return array
     */
    public function all()
    {
        return $this->errors;
    }

    /**
     * Clear all validation error messages
     *
     * @return void
     */
    public function clear()
    {
        $this->errors = [];
    }
}