<?php

namespace Core\Database;

use Core\Database\Caster;
use Core\Database\QueryBuilder;
use Exception;

class Model
{
    private static $instance;

    /**
     * The current model class name
     *
     * @var object
     */
    protected $model;

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
     * The QueryBuilder instance
     *
     * @var Core\Database\QueryBuilder
     */
    public $builder;

    /**
     * The result caster instance
     *
     * @var Core\Database\Caster;
     */
    protected $caster;

    /**
     * Wether the current table use timestamps or not
     *
     * @var boolean
     */
    protected $timestamps = true;

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
     * @return null|mixed
     */
    public function __get($key)
    {
        if (!array_key_exists($key, $this->columns)) {
            return null;
        }

        return $this->columns[$key];
    }

    protected $defaultCasts = [
        'created_at' => 'date',
        'updated_at' => 'date',
        'deleted_at' => 'date'
    ];

    /**
     * Column casts
     *
     * @var array
     */
    protected $casts = [];

    public function __construct()
    {
        $this->builder  = new QueryBuilder;
        $this->caster   = new Caster($this->getCasts());
        $this->model    = $this->getName();

        $this->determineTableName();
    }

    private function determineTableName()
    {
        if (is_null($this->table)) {
            $path = explode('\\', $this->model);
            $class = array_pop($path);

            $this->table = strtolower($class) . 's';
        }
    }

    private static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static;
        }

        return self::$instance;
    }

    public function __call($method, $args)
    {
        $instance = static::getInstance();

        return $instance->call($instance, $method, ...$args);
    }

    public static function __callStatic($method, $args)
    {
        $instance = static::getInstance();

        return $instance->call($instance, $method, ...$args);
    }

    private function getCasts()
    {
        $filteredCasts = array_filter($this->casts, function($cast) {
            return !in_array($cast, array_keys($this->defaultCasts));
        }, ARRAY_FILTER_USE_KEY);

        if ($this->timestamps) {
            return array_merge($filteredCasts, $this->defaultCasts);
        }

        return $filteredCasts;
    }

    protected function getName()
    {
        return get_called_class();
    }

    private function call($object, $method, ...$args)
    {
        $methodName = 'call'.ucwords($method);

        if (!method_exists($object, $methodName)) {
            throw new Exception("Method [{$method}] doesn't exists on class [{$object->getName()}]");
        }

        return $object->{$methodName}(...$args);
    }

     /**
     * Store the data to the given table
     *
     * @param string    $table
     * @param array     $data
     * @return void
     */
    public function callCreate($data)
    {
        if ($this->timestamps) {
            $now                = date('Y-m-d H:i:s');
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
        }

        $this->builder->insert($this->table, $data);
    }

    public function callWhere(...$conditions)
    {
        $this->builder->where(...$conditions);

        return $this;
    }

    public function callHaving(...$conditions)
    {
        $this->builder->having(...$conditions);

        return $this;
    }

    public function callGroup(...$conditions)
    {
        $this->builder->group(...$conditions);

        return $this;
    }

    public function callOrder(...$options)
    {
        $this->builder->order(...$options);

        return $this;
    }

    public function callTake($length)
    {
        $this->builder->take($length);

        return $this;
    }

    public function callSkip($length)
    {
        $this->builder->skip($length);

        return $this;
    }

    public function callGet(...$columns)
    {
        $this->select($this->table, ...$columns);

        if ($this->timestamps) {
            $this->order('created_at', 'ASC');
        }

        return $this->caster->cast(
            $this->builder->fetch($this->model)
        );
    }

    public function callFirst(...$columns)
    {
        $this->select($this->table, ...$columns);

        return $this->caster->cast(
            $this->builder->first($this->model)
        );
    }

    public function callSelect(...$columns)
    {
        $this->builder->select(...$columns);
        return $this;
    }

    public function callDelete()
    {
        $this->builder->delete($this->table);
    }
}

?>