<?php

namespace App\Core\Database;

use DateTime;
use PDO;
use App\Core\Registry;

class Model
{
    /**
     * The PHP PDO instance
     *
     * @var PDO
     */
    private $pdo = null;

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table;

    /**
     * All database table columns associated to the model
     *
     * @var array
     */
    protected $columns = [];

    /**
     * The generated SQL Query
     *
     * @var string
     */
    protected $query;

    /**
     * Set a column with a specified value
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->columns[$key] = $value;
    }

    /**
     * Get a value with a specific column
     *
     * @param string $key
     * @return null | mixed
     */
    public function __get($key)
    {
        if (!array_key_exists($key, $this->columns)) {
            return null;
        }

        return $this->columns[$key];
    }

    /**
     * Column casts
     *
     * @var array
     */
    protected $casts = [];

    public function __construct()
    {
        $connection = Registry::get('connection');

        $this->pdo  = $connection->getPDO();
    }

    /**
     * Construct SQL `INSERT` query
     *
     * @param array $data
     * @return string
     */
    private function buildInsertQuery($data)
    {
        $query  = "INSERT INTO {$this->table} (";
        $keys   = array_keys($data);
        
        $query  .= implode(', ', $keys) . ') VALUES (';

        foreach ($keys as $key => $column) {
            $query .= ":{$column}";
            
            $lastKeyIndex = count($keys) - 1;

            if ($key < $lastKeyIndex) {
                $query .= ', ';
            }
        }

        $query .= ')';

        return $query;
    }

    /**
     * Construct SQL `SELECT` query
     *
     * @param string $table
     * @param array $columns
     * @param string $additional
     * @return string
     */
    private function buildSelectQuery($columns = [])
    {
        $query          = "SELECT ";
        $columnsLength  = count($columns);

        if (($columnsLength === 1) &&
            (is_array($columns[0]))) {
            $query  .= implode(', ', $columns[0]);
        } elseif ($columnsLength > 1) {
            $query  .= implode(', ', $columns);
        } else {
            $query  .= '*';
        }

        $query .= " FROM {$this->table}";

        return $query;
    }

    /**
     * Build SQL parameters binding
     * Example Result: [':column' => 'value']
     *
     * @param array $data
     * @return array
     */
    private function buildBindParams($data)
    {
        $params = [];

        foreach ($data as $key => $value) {
            $params[":{$key}"] = $value;
        }

        return $params;
    }

    /**
     * Store the data to the given table
     *
     * @param string $table
     * @param array $data
     * @return void
     */
    public function insert($data)
    {
        $query      = $this->buildInsertQuery($data);
        $params     = $this->buildBindParams($data);
        $statement  = $this->pdo->prepare($query);
        
        $statement->execute($params);
    }

    /**
     * Get all data from the given table
     *
     * @param string $table
     * @param array $columns
     * @param string $additional
     * @return array
     */
    public function select(...$columns)
    {
        $this->query = $this->buildSelectQuery($columns);

        return $this;
    }

    /**
     * Check if options passed to order method is only single order
     *
     * @param array $options
     * @return boolean
     */
    private function isSingleOrderOption($options)
    {
        if (count($options) !== 2) {
            return false;
        }

        foreach ($options as $option) {
            if (!is_string($option)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Build multiple order query if the options passed to order method is array
     *
     * @param array $options
     * @return string
     */
    private function buildMultipleOrderQuery($options)
    {
        $query  = ' ORDER BY ';
        $orders = [];

        foreach ($options as $key => $option) {
            $orderDirection = strtoupper($option);
            $orders[]       = "{$key} {$orderDirection}";
        }

        $query .= implode(', ', $orders);

        return $query;
    }

    /**
     * Order query 'ORDER BY'
     *
     * @param string | array ...$options
     * @return QueryBuilder
     */
    public function order(...$options)
    {
        if (count($options) === 0) {
            return $this;
        }

        if ($this->isSingleOrderOption($options)) {
            $orderColumn    = $options[0];
            $orderDirection = strtoupper($options[1]);

            $this->query    .= " ORDER BY {$orderColumn} {$orderDirection}";

            return $this;
        }

        $this->query .= $this->buildMultipleOrderQuery($options[0]);
        
        return $this;
    }

    /**
     * Fetch only one row from the database
     *
     * @return mixed
     */
    public function first()
    {
        $this->query    .= ' LIMIT 1';

        $statement      = $this->pdo->prepare($this->query);
        $childClass     = get_called_class();

        $statement->execute();

        $result = $statement->fetchObject($childClass);

        return $this->castResult($result);
    }

    /**
     * Fetch all object retrieved from the query
     *
     * @return mixed
     */
    public function get()
    {
        $statement  = $this->pdo->prepare($this->query);
        $childClass = get_called_class();

        $statement->execute();

        $result = $statement->fetchAll(PDO::FETCH_CLASS, $childClass);

        return $this->castResult($result);
    }

    /**
     * Cast the result if there are casts assigned
     *
     * @param array | object $result
     * @return array
     */
    private function castResult($result)
    {
        if (count($this->casts) === 0) {
            return $result;
        }

        if (is_object($result)) {
            return $this->castSingleResult($result);
        }

        return $this->castMultipleResult($result);
    }

    /**
     * Casts all columns which specified in casts in a single result object
     *
     * @param object $result
     * @return object
     */
    private function castSingleResult($result)
    {
        foreach ($this->casts as $key => $cast) {
            $result->{$key} = $this->castColumn($cast, $result->{$key});
        }

        return $result;
    }

    /**
     * Casts all columns which specified in casts in multiple result object
     *
     * @param array $result
     * @return array
     */
    private function castMultipleResult($result)
    {
        foreach ($result as $key => $model) {
            foreach ($model->casts as $key => $cast) {
                $model->{$key} = $this->castColumn($cast, $model->{$key});
            }
        }

        return $result;
    }

    /**
     * Determine which cast should be applied to specific column
     *
     * @param string $cast
     * @param mixed $value
     * @return mixed
     */
    private function castColumn($cast, $value)
    {
        switch ($cast) {
            case 'date':
                return $this->castToDate($value);
        }
    }

    /**
     * Casting for 'date'
     *
     * @param string $value
     * @return DateTime
     */
    private function castToDate($value)
    {
        if (!$value) {
            return null;
        }

        return DateTime::createFromFormat('Y-m-d H:i:s', $value);
    }
}

?>