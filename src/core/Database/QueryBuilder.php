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
     * Table name
     *
     * @var string
     */
    protected $table = null;

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
        '>', '<', '=', '!=', '>=',
        '<=', '<>', 'is', 'is not'
    ];

    /**
     * List of all primary parameter bindings
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * List of all deferred parameter bindings
     *
     * @var array
     */
    protected $deferred = [];

    /**
     * List of all binding types allowed
     *
     * @var array
     */
    protected $bindingTypes = [
        'integer'   => PDO::PARAM_INT,
        'string'    => PDO::PARAM_STR,
        'boolean'   => PDO::PARAM_BOOL,
        'null'      => PDO::PARAM_NULL
    ];

    /**
     * List of all queries specific to their type
     *
     * @var array
     */
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
     * Build the SQL 'INSERT' query
     *
     * @param   array $data
     * @return  string
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

            $this->addBinding('value', $data[$column]);
        }

        return $query .= ')';
    }

    /**
     * Build the SQL `UPDATE` query
     *
     * @param   array $data
     * @return  string
     */
    private function buildUpdateQuery($data)
    {
        $query  = "UPDATE {$this->table} SET";
        $fields = [];

        foreach ($data as $key => $value) {
            $fields[]           = "{$key} = ?";
            
            $this->addBinding('value', $value);
        }

        return $query .= ' ' . implode(', ', $fields);
    }

    /**
     * Construct SQL `SELECT` query
     *
     * @param   string    $table
     * @param   array     $columns
     * @param   string    $additional
     * @return  string
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

        return $query .= " FROM {$this->table}";
    }

    /**
     * Insert new data
     *
     * @param   string    $table
     * @param   array     $data
     * @return  bool|string
     */
    public function insert($table, $data)
    {
        $this->setTable($table);
        $this->setQuery($this->buildInsertQuery($data));

        return $this->exec();
    }

    /**
     * Update existing data
     *
     * @param   string    $table
     * @param   array     $data
     * @return  boolean|string
     */
    public function update($table, $data)
    {
        $this->setTable($table);
        $this->setQuery($this->buildUpdateQuery($data));

        return $this->exec();
    }

    /**
     * Delete existing data
     *
     * @param   string $table
     * @return  void
     */
    public function delete($table)
    {
        $this->setQuery("DELETE FROM $table");

        $this->exec();
    }

    /**
     * Fetch only one row from the database
     *
     * @return mixed
     */
    public function fetchOne($class)
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

    /**
     * Generates select query
     *
     * @param   string        $table
     * @param   string|array  $columns
     * @return  Core\Database\QueryBuilder
     */
    public function select($table, ...$columns)
    {
        $this->setTable($table);
        $this->setQuery($this->buildSelectQuery(...$columns));

        return $this;
    }

    /**
     * Generates order query
     *
     * @param   string|array ...$options
     * @return  Core\Database\QueryBuilder
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

    /**
     * Generates limit query
     *
     * @param   int $length
     * @return  Core\Database\QueryBuilder
     */
    public function take($length)
    {
        $this->setQueryChunk('limit', $length);

        return $this;
    }

    /**
     * Generates offset query
     *
     * @param   int $length
     * @return  Core\Database\QueryBuilder
     */
    public function skip($length)
    {
        $this->setQueryChunk('offset', $length);

        return $this;
    }

    /**
     * Generates where query
     *
     * @param   string|array ...$conditions
     * @return  Core\Database\QueryBuilder
     */
    public function where($params)
    {
        if (count($params) === 1) {
            return $this->buildSingleConditionQuery('wheres', $params);
        }

        return $this->buildMultipleConditionQuery('wheres', $params);
    }

    /**
     * Generate having query
     *
     * @param   string|array ...$conditions
     * @return  Core\Database\QueryBuilder
     */
    public function having($params)
    {
        if (count($params['conditions']) === 1) {
            return $this->buildSingleConditionQuery('havings', $params);
        }

        return $this->buildMultipleConditionQuery('havings', $params);
    }

    /**
     * Generate group query
     *
     * @param   string|array ...$conditions
     * @return  Core\Database\QueryBuilder
     */
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

    /**
     * Set the current query
     *
     * @param   string $query
     * @return  void
     */
    private function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * Set the current table name
     *
     * @param   string $name
     * @return  void
     */
    private function setTable($name)
    {
        if (is_null($this->table)) {
            $this->table = $name;
        }
    }

    /**
     * Build condition query like 'WHERE' or 'HAVING' if the options considered as single
     *
     * @param   string    $type
     * @param   array     $conditions
     * @return  Core\Database\QueryBuilder
     */
    private function buildSingleConditionQuery($type, $params)
    {
        $column     = $params['conditions'][0];
        $operator   = $params['conditions'][1];
        $value      = $params['conditions'][2];
        
        if ($this->operatorAllowed($operator)) {
            $operator = strtoupper($operator);

            $this->addBinding('param', $value, true);
            $this->addQueryChunk($type, "{$column} {$operator} ?", $params['options']);
        }

        return $this;
    }

    /**
     * Build condition query like 'WHERE' or 'HAVING' if the options considered as many
     *
     * @param   string    $type
     * @param   array     $conditions
     * @return  Core\Database\QueryBuilder
     */
    private function buildMultipleConditionQuery($type, $args)
    {
        $params = [];

        if (count($args) === 0) {
            return;
        }

        foreach ($args['conditions'] as $condition) {
            if (count($condition) < 3) {
                continue;
            }

            $column     = $condition[0];
            $operator   = $condition[1];
            $value      = $condition[2];

            if ($this->operatorAllowed($operator)) {
                $operator = strtoupper($operator);
                $params[] = "{$column} {$operator} ?";
            
                $this->addBinding('param', $value, true);
            }
        }

        if (count($params) > 0) {
            $this->addQueryChunk($type, implode(' AND ', $params), $args['options']);
        }

        return $this;
    }

    /**
     * Check if the given operator is allowed for querying
     *
     * @param   string $operator
     * @return  string
     */
    private function operatorAllowed($operator)
    {
        return in_array(strtolower($operator), $this->operators);
    }

    /**
     * Save all bindings from the query whether it should be main binding or deferred
     *
     * @param   string    $type
     * @param   mixed     $data
     * @param   boolean   $deferred
     * @return  void
     */
    private function addBinding($type, $data, $deferred = false)
    {
        if ($deferred) {
            $this->deferred[] = [
                'type'  => $type,
                'value' => $data
            ];
            return;
        }

        $this->bindings[] = [
            'type'  => $type,
            'value' => $data
        ];
    }

    /**
     * Add query chunk if they could be many values
     *
     * @param   string $name
     * @param   string $query
     * @return  void
     */
    private function addQueryChunk($name, $query, $options = null)
    {
        $chunk = [
            'query'     => $query,
            'order'     => $this->getQueryChunkOrder($this->chunks[$name]),
            'options'   => $options
        ];

        $this->chunks[$name][] = $chunk;
    }

    /**
     * Add query chunk if they considered as single value
     *
     * @param   string $name
     * @param   string $query
     * @return  void
     */
    private function setQueryChunk($name, $query)
    {
        $this->chunks[$name] = $query;
    }

    /**
     * Get an appropriate order with the given chunks
     *
     * @param   array $chunks
     * @return  int
     */
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

    /**
     * Assemble group queries form the chunks
     *
     * @return string
     */
    private function makeGroupQuery()
    {
        $groups     = $this->chunks['groups'];
        $queries    = [];

        foreach ($groups as $group) {
            $queries[] = $group['query'];
        }

        return implode(', ', $queries);
    }

    /**
     * Assemble query from 'limit' and 'offset' chunks
     *
     * @return string
     */
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

    /**
     * Assemble query that considered to be conditional like 'wheres' and 'havings'
     *
     * @param   string $type
     * @return  string
     */
    private function makeConditionalQuery($type)
    {
        $chunks = $this->chunks[$type];
        $query  = '';

        foreach ($chunks as $chunk) {
            $next   = next($chunks);
            $query  .= '('. $chunk['query'] .')';

            if ($next) {
                $query .= ' ' . strtoupper($next['options']['expr']) . ' ';
            }
        }

        return $query;
    }

    /**
     * Assemble all order queries from the chunks
     *
     * @return string
     */
    private function makeOrderQuery()
    {
        $orders     = $this->chunks['orders'];
        $queries    = [];

        foreach ($orders as $order) {
            $queries[] = $order['query'];
        }

        return implode(', ', $queries);
    }

    /**
     * Check if options passed to order method is only single order
     *
     * @param   array $options
     * @return  boolean
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
     * @param   array $options
     * @return  string
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
     * Assemble a full query following their specific order
     *
     * @return string
     */
    private function buildFullquery()
    {
        $query      = $this->query;
        $wheres     = $this->makeConditionalQuery('wheres');
        $groups     = $this->makeGroupQuery();
        $havings    = $this->makeConditionalQuery('havings');
        $orders     = $this->makeOrderQuery();
        $limit      = $this->chunks['limit'];
        $offset     = $this->chunks['offset'];

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

        if ((!is_null($limit)) &&
            ($limit !== '')) {
            $query .= ' LIMIT ' . $limit;
        }

        if ((!is_null($limit)) &&
            ($limit !== '') &&
            (!is_null($offset)) &&
            ($offset !== '')) {
            $query .= ' OFFSET ' . $offset;
        }

        return $query;
    }

    /**
     * Execute the current query
     *
     * @return PDOStatement
     */
    private function exec()
    {
        $statement = $this->pdo->prepare($this->buildFullquery());

        $this->execBindings($statement);
        $statement->execute();

        $this->flush();

        return $statement;
    }

    /**
     * Execute all parameters bindings
     *
     * @param   PDOStatement $statement
     * @return  void
     */
    private function execBindings($statement)
    {
        $bindings = array_merge($this->bindings, $this->deferred);

        foreach ($bindings as $key => $binding) {
            $type           = gettype($binding['value']);
            $bindingType    = !in_array($type, array_keys($this->bindingTypes)) ?
                                    $this->bindingTypes['string'] : $this->bindingTypes[$type];
            $bindMethod     = 'bind' . ucfirst($binding['type']);

            $statement->{$bindMethod}($key+1, $binding['value'], $bindingType);
        }
    }

    /**
     * Flush query, table, bindings and chunks
     *
     * @return void
     */
    private function flush()
    {
        $this->setQuery('');
        $this->setTable(null);

        $this->flushBindings();
        $this->flushChunks();
    }

    /**
     * Flush the main and deferred bindings
     *
     * @return void
     */
    private function flushBindings()
    {
        $this->bindings = [];
        $this->deferred = [];
    }

    /**
     * Flush all query chunks
     *
     * @return void
     */
    private function flushChunks()
    {
        $this->chunks = [
            'wheres'    => [],
            'orders'    => [],
            'groups'    => [],
            'havings'   => [],
            'limit'     => null,
            'offset'    => null
        ];
    }
}