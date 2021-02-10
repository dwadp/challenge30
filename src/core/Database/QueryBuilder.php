<?php

namespace Core\Database;

use PDO;

class QueryBuilder
{
    /**
     * The PHP PDO instance
     *
     * @var PDO
     */
    private $pdo = null;

    /**
     * The generated SQL Query
     *
     * @var string
     */
    protected $query;

    /**
     * Available query operators
     *
     * @var array
     */
    protected $operators = [
        '>', '<', '=', '!='
    ];

    /**
     * Save the current query bindings
     *
     * @var array
     */
    protected $bindings = [];

    protected $bindingTypes = [
        'integer' => PDO::PARAM_INT,
        'string' => PDO::PARAM_STR,
        'boolean' => PDO::PARAM_BOOL
    ];

    /**
     * Table name
     *
     * @var string
     */
    protected $table = null;

    protected $deferred = [];

    protected $chunks = [
        'wheres'    => [],
        'orders'    => [],
        'groups'    => [],
        'havings'   => [],
        'limit'     => null,
        'offset'    => null
    ];

    public function __construct()
    {
        $connection = app()->get('connection');

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
            $query .= "?";
            
            $lastKeyIndex = count($keys) - 1;

            if ($key < $lastKeyIndex) {
                $query .= ', ';
            }

            $this->bindings[] = $data[$column];
        }

        $query .= ')';

        return $query;
    }

    /**
     * Construct SQL `SELECT` query
     *
     * @param string    $table
     * @param array     $columns
     * @param string    $additional
     * @return string
     */
    private function buildSelectQuery(...$columns)
    {
        $query          = "SELECT ";
        $columnsLength  = count($columns);

        if ($columnsLength === 0) {
            $query .= '*';
        } else {
            $actualColumns  = is_array($columns[0]) ? $columns[0] : $columns;
            $query          .= implode(', ', $actualColumns);
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
     * @param string    $table
     * @param array     $data
     * @return void
     */
    public function insert($table, $data)
    {
        $this->setTable($table);

        $this->query = $this->buildInsertQuery($data);

        $this->exec();
    }

    /**
     * Get all data from the given table
     *
     * @param string    $table
     * @param array     $columns
     * @param string    $additional
     * @return array
     */
    public function select($table, ...$columns)
    {
        $this->setTable($table);

        $this->query = $this->buildSelectQuery(...$columns);

        return $this;
    }

    private function setTable($name)
    {
        if (is_null($this->table)) {
            $this->table = $name;
        }
    }

    public function where(...$conditions)
    {
        if (count($conditions) > 1) {
            return $this->buildSingleConditionQuery('wheres', $conditions);
        }

        return $this->buildMultipleConditionQuery('wheres', $conditions);
    }

    public function having(...$conditions)
    {
        if (count($conditions) > 1) {
            return $this->buildSingleConditionQuery('havings', $conditions);
        }

        return $this->buildMultipleConditionQuery('havings', $conditions);
    }

    public function group(...$groups)
    {
        $columns = [];

        if (count($groups) === 1 && is_array($groups[0])) {
            $columns = $groups[0];
        } else {
            $columns = $groups;
        }

        $this->addQueryChunk('groups', implode(', ', $columns));
        return $this;
    }

    private function buildSingleConditionQuery($type, $conditions)
    {
        $column     = $conditions[0];
        $operator   = $conditions[1];
        $value      = $conditions[2];

        $fields = $this->addParamBinding($value);
        $this->addQueryChunk($type, "{$column} {$operator} ?");

        return $this;
    }

    private function addParamBinding($data)
    {
        $this->bindings[] = $data;
    }

    private function getLastParamBinding($bindings)
    {
        if (count($bindings) === 0) {
            return 1;
        }

        $last = $bindings;

        usort($last, function($prev, $current) {
            if ($prev['order'] === $current['order']) {
                return 0;
            }

            return ($prev['order'] < $current['order']) ? -1 : 1;
        });

        $lastOrder = (int) $last[0]['order'];

        return $lastOrder + 1;
    }

    private function addQueryChunk($name, $query)
    {
        $chunk = [
            'query' => $query,
            'order' => $this->getQueryChunkOrder($this->chunks[$name])
        ];

        $this->chunks[$name][] = $chunk;
    }

    private function getQueryChunkOrder($chunks)
    {
        if (count($chunks) === 0) {
            return 1;
        }

        $last = $chunks;

        usort($last, function($prev, $current) {
            if ($prev['order'] === $current['order']) {
                return 0;
            }

            return ($prev['order'] < $current['order']) ? 1 : -1;
        });

        $lastOrder = (int) $last[0]['order'];

        return $lastOrder + 1;
    }

    private function setQueryChunk($name, $query)
    {
        $this->chunks[$name] = $query;
    }

    public function delete($table)
    {
        $this->query = "DELETE FROM $table";

        $this->exec();
    }

    private function buildMultipleConditionQuery($type, $conditions)
    {
        $params = [];
        $binds  = [];

        if (count($conditions) === 0) {
            return;
        }

        foreach ($conditions[0] as $key => $condition) {
            if (count($condition) < 3) {
                continue;
            }

            $column     = $condition[0];
            $operator   = $condition[1];
            $value      = $condition[2];

            $columnBind = $column;

            if (array_key_exists($column, $binds)) {
                $columnBind = $columnBind . '_' . $key;
            }

            $params[] = "{$column} {$operator} ?";
            
            $this->addParamBinding($value);
        }

        $this->addQueryChunk($type, implode(' AND ', $params));

        return $this;
    }

    private function makeConditionalQuery($type)
    {
        $chunk = $this->chunks[$type];
        $queries = [];

        foreach ($chunk as $query) {
            $queries[] = '('. $query['query'] .')';
        }

        return implode(' AND ', $queries);
    }

    private function makeGroupQuery()
    {
        $groups = $this->chunks['groups'];
        $queries = [];

        foreach ($groups as $group) {
            $queries[] = $group['query'];
        }

        return implode(', ', $queries);
    }

    private function makeLimitQuery()
    {
        $limit  = $this->chunks['limit'];
        $offset = $this->chunks['offset'];
        $query  = '';

        if (!is_null($offset) && $offset !== '') {
            $query .= $offset . ', ';
        }

        if (!is_null($limit) && $limit !== '') {
            $query .= $limit;
        }

        return $query;
    }

    private function makeOrderQuery()
    {
        $orders = $this->chunks['orders'];
        $queries = [];

        foreach ($orders as $where) {
            $queries[] = $where['query'];
        }

        return implode(', ', $queries);
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
        $orders = [];

        foreach ($options as $key => $option) {
            $orderDirection = strtoupper($option);
            $orders[]       = "{$key} {$orderDirection}";
        }

        return implode(', ', $orders);
    }

    /**
     * Order query 'ORDER BY'
     *
     * @param string|array ...$options
     * @return QueryBuilder
     */
    public function order(...$options)
    {
        if (count($options) === 0) {
            return $this;
        }

        $query = '';

        if ($this->isSingleOrderOption($options)) {
            $orderColumn    = $options[0];
            $orderDirection = strtoupper($options[1]);

            $query = "{$orderColumn} {$orderDirection}";
        } else {
            $query = $this->buildMultipleOrderQuery($options[0]);
        }

        $this->addQueryChunk('orders', $query);
        
        return $this;
    }

    public function take($length)
    {
        $this->setQueryChunk('limit', $length);
    }

    public function skip($length)
    {
        $this->setQueryChunk('offset', $length);
    }

    /**
     * Fetch only one row from the database
     *
     * @return mixed
     */
    public function first($class)
    {
        $this->take(1);

        return $this->exec()->fetchObject($class);
    }

    /**
     * Fetch all object retrieved from the query
     *
     * @return mixed
     */
    public function fetch($class)
    {
        return $this->exec()->fetchAll(PDO::FETCH_CLASS, $class);
    }

    private function buildFullquery()
    {
        $query      = $this->query;
        $wheres     = $this->makeConditionalQuery('wheres');
        $groups     = $this->makeGroupQuery();
        $havings    = $this->makeConditionalQuery('havings');
        $orders     = $this->makeOrderQuery();
        $limit      = $this->makeLimitQuery();

        if ($wheres !== '') {
            $query .= ' WHERE ' . $wheres;
        }

        if ($groups !== '') {
            $query .= ' GROUP BY ' . $groups;
        }

        if ($havings !== '') {
            $query .= ' HAVING ' . $havings;
        }

        if ($orders !== '') {
            $query .= ' ORDER BY ' . $orders;
        }

        if ($limit !== '') {
            $query .= ' LIMIT ' . $limit;
        }

        return $query;
    }

    private function exec()
    {
        $statement = $this->pdo->prepare($this->buildFullquery());

        $this->execBindings($statement);
        $statement->execute();

        $statement->debugDumpParams();

        return $statement;
    }

    private function flush()
    {
        $this->query = '';
        $this->setTable(null);
        $this->bindings = [];
    }

    private function execBindings($statement)
    {
        foreach ($this->bindings as $key => $binding) {
            $type           = gettype($binding);
            $bindingType    = !in_array($type, array_keys($this->bindingTypes)) ?
                                    $this->bindingTypes['string'] : $this->bindingTypes[$type];

            $statement->bindValue($key+1, $binding, $bindingType);
        }
    }
}