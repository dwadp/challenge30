<?php

namespace Core\Database;

use Core\Database\Caster;
use Core\Database\QueryBuilder;
use Exception;

class Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table;

    /**
     * List of all columns that allowed to be filled in
     *
     * @var array
     */
    protected $fillable  = [];

    /**
     * Lists of all columns in the database table
     *
     * @var array
     */
    protected $columns  = [];

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
     * Wether the current model using a soft delete
     *
     * @var boolean
     */
    protected $softDeletes = false;

    /**
     * Wether the query should include previously soft deleted data
     *
     * @var boolean
     */
    protected $includeSoftDeleted = false;

    /**
     * Already selected will prevent multiple select query on the same model instance
     *
     * @var boolean
     */
    private $alreadySelected = false;

    /**
     * List of all common default casts
     *
     * @var array
     */
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

        $this->determineTableName();
    }

    /**
     * Set a column with a specified value
     *
     * @param string    $key
     * @param mixed     $value
     */
    public function __set($key, $value)
    {
        $this->columns[$key] = $value;
    }

    /**
     * Get a value with a specific column
     *
     * @param   string $key
     * @return  null|mixed
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->columns)) {
            return $this->columns[$key];
        }
    }

    /**
     * Set the associated table name
     *
     * @return void
     */
    private function determineTableName()
    {
        if (is_null($this->table)) {
            $path           = explode('\\', $this->getName());
            $class          = array_pop($path);

            $this->table    = strtolower($class) . 's';
        }
    }

    /**
     * Handle all method call associated to the model
     *
     * @param   string    $method
     * @param   mixed     $args
     * @return  mixed
     */
    public function __call($name, $args)
    {
        return $this->call($name, ...$args);
    }

    /**
     * Handle all static method call associated to the model
     *
     * @param   string    $method
     * @param   mixed     $args
     * @return  mixed
     */
    public static function __callStatic($name, $args)
    {
        return (new static)->call($name, ...$args);
    }

    /**
     * Handle method call and forward to the actual method
     *
     * @param   object        $object
     * @param   string        $method
     * @param   mixed|array   ...$args
     * @return  mixed
     */
    private function call($name, ...$args)
    {
        $method = 'call' . ucwords($name);

        if (!method_exists($this, $method)) {
            throw new Exception("Method [{$name}] doesn't exists on class [{$this->getName()}]");
        }

        return $this->$method(...$args);
    }

    /**
     * Merge the default casts with the model specific casts
     *
     * @return array
     */
    private function getCasts()
    {
        $casts = array_filter($this->casts, function($cast) {
            return !in_array($cast, array_keys($this->defaultCasts));
        }, ARRAY_FILTER_USE_KEY);

        if ($this->timestamps) {
            return array_merge($casts, $this->defaultCasts);
        }

        return $casts;
    }

    /**
     * Check if the model using blacklists
     *
     * @return boolean
     */
    private function useBlacklists()
    {
        return ((count($this->blacklists) > 0) && 
                (count($this->whitelists) === 0));
    }

    /**
     * Filter the data before proceeding to the next function calls
     *
     * @param   array $data
     * @return  array
     */
    private function filterData($data)
    {
        // If fillable empty, then just return the whole data
        if (count($this->fillable) === 0) {
            return $data;
        }

        return array_filter($data, function($field) {
            return in_array($field, $this->fillable);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get the specific model name
     *
     * @return string
     */
    protected function getName()
    {
        return get_called_class();
    }

     /**
     * Insert a new data
     *
     * @param   string    $table
     * @param   array     $data
     * @return  void
     */
    public function callCreate($data)
    {
        $data = $this->filterData($data);

        if ($this->timestamps) {
            $now                = now('Y-m-d H:i:s');
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
        }

        $this->builder->insert($this->table, $data);
    }

    /**
     * Update an existing data
     *
     * @param   string    $table
     * @param   array     $data
     * @return  void
     */
    public function callUpdate($data)
    {
        $data = $this->filterData($data);

        if ($this->timestamps) {
            $data['updated_at'] = now('Y-m-d H:i:s');
        }

        $this->builder->update($this->table, $data);
    }

    /**
     * Build SQL where query
     *
     * @param   mixed|array $args
     * @return  Core\Database\Model
     */
    public function callWhere($args)
    {
        $params = $this->getConditionalParameters(
            is_array($args) ? $args : [func_get_args()],
            ['expr' => 'and']
        );

        $this->builder->where($params);

        return $this;
    }

    /**
     * Build SQL or where query
     *
     * @param   mixed|array $args
     * @return  Core\Database\Model
     */
    public function callOrWhere($args)
    {
        $params = $this->getConditionalParameters(
            is_array($args) ? $args : [func_get_args()],
            ['expr' => 'or']
        );

        $this->builder->where($params);

        return $this;
    }

    /**
     * Build SQL having query
     *
     * @param   mixed|array $args
     * @return  Core\Database\Model
     */
    public function having($args)
    {
        $params = $this->getConditionalParameters(
            is_array($args) ? $args : [func_get_args()],
            ['expr' => 'and']
        );

        $this->builder->having($params);

        return $this;
    }

    /**
     * Build SQL having query
     *
     * @param   mixed|array $args
     * @return  Core\Database\Model
     */
    public function orHaving($args)
    {
        $params = $this->getConditionalParameters(
            is_array($args) ? $args : [func_get_args()],
            ['expr' => 'or']
        );

        $this->builder->having($params);

        return $this;
    }

    /**
     * Build SQL group query
     *
     * @param   mixed|array ...$groups
     * @return  Core\Database\Model
     */
    public function group(...$groups)
    {
        $this->builder->group(...$groups);

        return $this;
    }

    /**
     * Build SQL order query
     *
     * @param   mixed|array ...$options
     * @return  Core\Database\Model
     */
    public function callOrder(...$options)
    {
        $this->builder->order(...$options);

        return $this;
    }

    /**
     * Build SQL limit query
     *
     * @param   int $length
     * @return  Core\Database\Model
     */
    public function callTake($length)
    {
        $this->builder->take($length);

        return $this;
    }

    /**
     * Build SQL skip query
     *
     * @param   int $length
     * @return  Core\Database\Model
     */
    public function callSkip($length)
    {
        $this->builder->skip($length);

        return $this;
    }

    /**
     * Fetch multiple data from the current query
     *
     * @return  array|null
     */
    public function callAll(...$columns)
    {
        if (!$this->alreadySelected) {
            $this->builder->select($this->table, ...$columns);
        }

        if ($this->softDeletes && !$this->includeSoftDeleted) {
            $this->callWhere('deleted_at', 'is', null);
        }

        if ($this->timestamps) {
            $this->callOrder('created_at', 'desc');
        }

        return $this->caster->cast(
            $this->builder->fetch($this->getName())
        );
    }

    /**
     * Fetch only one data from the current query
     *
     * @return  array|null
     */
    public function callSingle(...$columns)
    {
        if (!$this->alreadySelected) {
            $this->builder->select($this->table, ...$columns);
        }

        if ($this->softDeletes && !$this->includeSoftDeleted) {
            $this->callWhere('deleted_at', 'is', null);
        }

        return $this->caster->cast(
            $this->builder->fetchOne($this->getName())
        );
    }

    /**
     * Build SQL select query
     *
     * @param   mixed|array ...$columns
     * @return  Core\Database\Model
     */
    public function callGet(...$columns)
    {
        $this->alreadySelected = true;

        $this->builder->select($this->table, ...$columns);

        return $this;
    }

    /**
     * Build SQL delete query
     *
     * @return void
     */
    public function callDelete()
    {
        if ($this->softDeletes) {
            return $this->softDelete();
        }

        $this->builder->delete($this->table);
    }

    /**
     * Force delete for model that uses soft delete
     *
     * @return void
     */
    public function callForceDelete()
    {
        $this->builder->delete($this->table);
    }

    /**
     * Restore data that previously soft deleted
     *
     * @return void
     */
    public function callRestore()
    {
        $this->builder->update($this->table, [
            'deleted_at' => null
        ]);
    }

    /**
     * Include previously deleted data
     *
     * @return Core\Database\Model
     */
    public function callWithDeleted()
    {
        $this->includeSoftDeleted = true;

        return $this;
    }

    /**
     * Perform soft deletion for current model
     *
     * @return void
     */
    private function softDelete()
    {
        $this->builder->update($this->table, [
            'deleted_at' => now('Y-m-d H:i:s')
        ]);
    }

    /**
     * Construct an appropriate parameters for conditional query for 'where' or 'having'
     *
     * @param   array   $args
     * @param   array   $options
     * @return  array
     */
    private function getConditionalParameters($args, $options)
    {
        return [
            'conditions'    => $args,
            'options'       => $options
        ];
    }
}

?>