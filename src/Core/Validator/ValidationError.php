<?php

namespace App\Core\Validator;

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
     * @param string $key
     * @param string $value
     * @return void
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
     * @param string $key
     * @return boolean
     */
    public function has($field)
    {
        if (!isset($this->errors[$field])) {
            return false;
        }

        return true;
    }

    /**
     * Get the first error message captured by a specific field/key
     *
     * @param string $key
     * @return null|string
     */
    public function get($field)
    {
        if (!$this->has($field)) {
            return null;
        }

        $errors = $this->errors[$field];

        if (count($errors) === 0) {
            return null;
        }

        return $errors[0];
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