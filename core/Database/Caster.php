<?php

namespace Core\Database;

use DateTime;

class Caster
{
    /**
     * Available caster type
     *
     * @var array
     */
    private $availableCasts = [
        'date',
        'int',
        'boolean',
        'string'
    ];

    /**
     * Cast column list
     *
     * @var array
     */
    private $casts = [];

    public function __construct($casts)
    {
        $this->casts = $casts;
    }

    /**
     * Cast array or object
     *
     * @param   array|object $result
     * @return  array
     */
    public function cast($result)
    {
        if (is_object($result)) {
            return $this->castSingleResult($this->casts, $result);
        }

        return $this->castMultipleResult($this->casts, $result);
    }

    /**
     * Casts all columns which specified in casts in a single result object
     *
     * @param   object $result
     * @return  object
     */
    private function castSingleResult($casts, $result)
    {
        if ($result) {
            foreach ($casts as $key => $cast) {
                $result->{$key} = $this->castColumn($cast, $result->{$key});
            }
    
            return $result;
        }
    }

    /**
     * Casts all columns which specified in casts in multiple result object
     *
     * @param   array $result
     * @return  array
     */
    private function castMultipleResult($casts, $result)
    {
        if (!$result) {
            return [];
        }

        foreach ($result as $key => $model) {
            foreach ($casts as $key => $cast) {
                $model->{$key} = $this->castColumn($cast, $model->{$key});
            }
        }

        return $result;
    }

    /**
     * Check if the current cast can be applied
     *
     * @param   string $cast
     * @return  boolean
     */
    private function canBeCasted($cast)
    {
        return in_array($cast, $this->availableCasts);
    }

    /**
     * Determine which cast should be applied to specific column
     *
     * @param   string    $cast
     * @param   mixed     $value
     * @return  mixed
     */
    private function castColumn($cast, $value)
    {
        if (!$this->canBeCasted($cast)) {
            return $value;
        }

        $handler = 'castTo'.$this->makeCastHandlerName($cast);

        if (method_exists($this, $handler)) {
            return call_user_func_array([$this, $handler], [$value]);
        }
    }

    /**
     * Build caster handler name
     *
     * @param   string $cast
     * @return  string
     */
    private function makeCastHandlerName($cast)
    {
        return ucwords(str_replace('_', '', $cast));
    }

    /**
     * Casting for 'date'
     *
     * @param   string $value
     * @return  DateTime
     */
    private function castToDate($value)
    {
        if (!$value) {
            return null;
        }

        return DateTime::createFromFormat('Y-m-d H:i:s', $value);
    }

    /**
     * Casting for 'int'
     *
     * @param   string $value
     * @return  int|null
     */
    private function castToInt($value)
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
    }

    /**
     * Casting for 'string'
     *
     * @param   string $value
     * @return  int|null
     */
    private function castToString($value)
    {
        return (string) $value;
    }

    /**
     * Casting for 'boolean'
     *
     * @param   string $value
     * @return  boolean
     */
    private function castToBoolean($value)
    {
        if (is_bool($value)) {
            return (bool) $value;
        }

        if (is_numeric($value)) {
            return $value === 0 ? false : true;
        }
    }
}