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
     * @param   array $rules
     * @param   array $data
     * @return  void
     */
    public function validate($rules, $data)
    {
        foreach ($rules as $key => $rule) {
            if (array_key_exists($key, $data)) {
                $this->requests[$key] = trim($data[$key]);

                $this->validateIndividual($rule, $key, $data[$key]);
            }
        }
    }

    /**
     * Validate individual field with the given rules and value
     *
     * @param   array     $rules
     * @param   string    $key
     * @param   mixed     $value
     * @return  void
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
     * @param   string    $ruleKey
     * @param   mixed     $ruleValue
     * @param   string    $inputKey
     * @param   mixed     $inputValue
     * @return  void
     */
    private function validateRule($ruleKey, $ruleValue, $inputKey, $inputValue)
    {
        $handler = 'validate' . $this->getLabel($ruleKey);

        if (method_exists($this, $handler)) {
            $this->$handler($ruleValue, $inputKey, $inputValue);
        }
    }

    /**
     * Handle validate "required" rule
     *
     * @param   mixed $value
     * @return  boolean
     */
    private function validateRequired($rule, $field, $value)
    {
        if (($value === null) ||
            (trim($value) === '') || 
            ($value === 0)) {
            $this->error->set($field, "{$this->getLabel($field)} is required");
        }
    }

    /**
     * Generate a label
     *
     * @param   string $name
     * @return  string
     */
    private function getLabel($name)
    {
        return ucwords($name);
    }

    /**
     * Handle validate "minlength" rule
     *
     * @param   mixed $ruleValue
     * @param   mixed $inputValue
     * @return  boolean
     */
    private function validateMinLength($rule, $field, $value)
    {
        $inputLength = strlen(trim($value));

        if ($inputLength < $rule) {
            $this->error->set(
                $value,
                "{$this->getLabel($field)} must be at least {$rule} characters long"
            );
        }
    }

    /**
     * Handle validate "maxlength" rule
     *
     * @param   mixed $ruleValue
     * @param   mixed $inputValue
     * @return  boolean
     */
    private function validateMaxLength($rule, $field, $value)
    {
        $inputLength = strlen(trim($value));

        if ($inputLength > $rule) {
            $this->error->set(
                $value,
                "{$this->getLabel($field)} must be set at maximum of {$rule} characters long"
            );
        }
    }

    /**
     * Validate lengths of a string with min and max value
     *
     * @param   array $ruleValue
     * @param   mixed $inputValue
     * @return  boolean
     */
    private function validateLengths($rule, $field, $value)
    {
        $value  = trim($value);
        $from   = $rule[0];
        $to     = $rule[1];

        if ((strlen($value) < $from) ||
            (strlen($value) > $to)) {
            $this->error->set(
                $field,
                "{$this->getLabel($field)} must be {$from} to {$to} characters long"
            );
        }
    }
}