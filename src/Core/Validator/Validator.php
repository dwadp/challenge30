<?php

namespace Core\Validator;

use Core\Validator\ValidationError;

class Validator
{
    /**
     * The validation error instance
     *
     * @var App\Core\Validator\ValidationError
     */
    public $error;

    public function __construct()
    {
        $this->error = new ValidationError;
    }

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
     * @param array     $rules
     * @param string    $key
     * @param mixed     $value
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
     * @param string    $ruleKey
     * @param mixed     $ruleValue
     * @param string    $inputKey
     * @param mixed     $inputValue
     * @return void
     */
    private function validateRule($ruleKey, $ruleValue, $inputKey, $inputValue)
    {
        $label = ucfirst($inputKey);

        switch ($ruleKey) {
            case 'required':
                if (!$this->validateRequired($inputValue)) {
                    $this->error->set($inputKey, "{$label} is required");
                }
                break;
            case 'minlength':
                if (!$this->validateMinLength($ruleValue, $inputValue)) {
                    $this->error->set(
                        $inputKey,
                        "{$label} must be at least {$ruleValue} characters long"
                    );
                }
                break;
            case 'maxlength':
                if (!$this->validateMaxLength($ruleValue, $inputValue)) {
                    $this->error->set(
                        $inputKey,
                        "{$label} must be set at maximum of {$ruleValue} characters long"
                    );
                }
                break;
            case 'lengths':
                if (!$this->validateLengths($ruleValue, $inputValue)) {
                    $from   = $ruleValue[0];
                    $to     = $ruleValue[1];

                    $this->error->set(
                        $inputKey,
                        "{$label} must be {$from} to {$to} characters long"
                    );
                }
                break;
        }
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

    /**
     * Validate lengths of a string with min and max value
     *
     * @param array $ruleValue
     * @param mixed $inputValue
     * @return boolean
     */
    private function validateLengths($ruleValue, $inputValue)
    {
        $value  = trim($inputValue);
        $from   = $ruleValue[0];
        $to     = $ruleValue[1];

        if ((strlen($value) < $from) ||
            (strlen($value) > $to)) {
            return false;
        }

        return true;
    }
}