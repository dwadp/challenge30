<?php

namespace App\Core;

class Validator
{
    /**
     * All errors recorded during validation
     *
     * @var array
     */
    private $errors = [];

    /**
     * All input request recorded during validation
     *
     * @var array
     */
    private $requests = [];

    /**
     * Validate with the given rules and data
     *
     * @param array $rules
     * @param array $data
     * @return void
     */
    public function validate($rules, $data)
    {
        foreach ($rules as $key => $rule) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $this->validateIndividual($rule, $key, $data[$key]);
            $this->requests[$key] = trim($data[$key]);
        }
    }

    /**
     * Validate individual field with the given rules and value
     *
     * @param array $rules
     * @param string $key
     * @param mixed $value
     * @return void
     */
    private function validateIndividual($rules, $key, $value)
    {
        foreach ($rules as $ruleKey => $ruleValue) {
            $this->validateRule($ruleKey, $ruleValue, $key, $value);
        }
    }

    /**
     * Determine how the input will be validated with the given rule 
     * and set the error based on the rule
     *
     * @param string $ruleKey
     * @param mixed $ruleValue
     * @param string $inputKey
     * @param mixed $inputValue
     * @return void
     */
    private function validateRule($ruleKey, $ruleValue, $inputKey, $inputValue)
    {
        $label = ucfirst($inputKey);

        switch ($ruleKey) {
            case 'required':
                if (!$this->validateRequired($inputValue)) {
                    $this->setError($inputKey, "{$label} is required");
                }
                break;
            case 'minlength':
                if (!$this->validateMinLength($ruleValue, $inputValue)) {
                    $this->setError(
                        $inputKey,
                        "{$label} must be at least {$ruleValue} characters long"
                    );
                }
                break;
            case 'maxlength':
                if (!$this->validateMaxLength($ruleValue, $inputValue)) {
                    $this->setError(
                        $inputKey,
                        "{$label} must be set at maximum of {$ruleValue} characters long"
                    );
                }
                break;
            case 'rangechars':
                if (!$this->validateRangeChars($ruleValue, $inputValue)) {
                    $from = $ruleValue['from'];
                    $to = $ruleValue['to'];

                    $this->setError(
                        $inputKey,
                        "{$label} must be {$from} to {$to} characters long"
                    );
                }
        }
    }

    /**
     * Set error message
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setError($key, $value)
    {
        $this->errors[$key][] = $value;
    }

    /**
     * Check if there's no error recorded
     *
     * @return boolean
     */
    public function errorsEmpty()
    {
        if (count($this->errors) === 0) {
            return true;
        }

        return false;
    }

    /**
     * Check if error exists by specific field/key
     *
     * @param string $key
     * @return boolean
     */
    public function has($key)
    {
        if (!isset($this->errors[$key])) {
            return false;
        }

        return true;
    }

    /**
     * Get the first error message captured by a specific field/key
     *
     * @param string $key
     * @return null | string
     */
    public function get($key)
    {
        if (!$this->has($key)) {
            return null;
        }

        $errors = $this->errors[$key];

        if (count($errors) === 0) {
            return null;
        }

        return $errors[0];
    }

    /**
     * Get previous input value
     *
     * @param string $key
     * @return mixed
     */
    public function old($key)
    {
        if (!array_key_exists($key, $this->requests)) {
            return null;
        }

        return $this->requests[$key];
    }

    /**
     * Get all error messages
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Clear all error messages
     *
     * @return void
     */
    public function clearErrors()
    {
        return $this->errors = [];
    }

    /**
     * Get all requests data that captured during validation
     *
     * @return array
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * Handle validate "required" rule
     *
     * @param mixed $value
     * @return boolean
     */
    private function validateRequired($value)
    {
        if (($value === null) ||
            (trim($value) === '') || 
            ($value === 0)) {
            return false;
        }

        return true;
    }

    /**
     * Handle validate "minlength" rule
     *
     * @param mixed $ruleValue
     * @param mixed $inputValue
     * @return boolean
     */
    private function validateMinLength($ruleValue, $inputValue)
    {
        $inputLength = strlen(trim($inputValue));

        if ($inputLength < $ruleValue) {
            return false;
        }

        return true;
    }

    /**
     * Handle validate "maxlength" rule
     *
     * @param mixed $ruleValue
     * @param mixed $inputValue
     * @return boolean
     */
    private function validateMaxLength($ruleValue, $inputValue)
    {
        $inputLength = strlen(trim($inputValue));

        if ($inputLength > $ruleValue) {
            return false;
        }

        return true;
    }

    private function validateRangeChars($ruleValue, $inputValue)
    {
        $value  = trim($inputValue);
        $from   = $ruleValue['from'];
        $to     = $ruleValue['to'];

        if ((strlen($value) < $from) ||
            (strlen($value) > $to)) {
            return false;
        }

        return true;
    }
}