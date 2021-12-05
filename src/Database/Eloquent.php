<?php

namespace Attla\Database;

use Attla\Encrypter;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model as EloquentModel;

abstract class Eloquent extends EloquentModel
{
    /**
     * Stores whether not to use query cache
     *
     * @var bool
     */
    protected static $withoutCache = false;

    public function __construct($attributes = [])
    {
        Encapsulator::getInstance();

        $this->appendEncodedId();

        if (!is_array($attributes)) {
            $attributes = (array) $attributes;
        }

        parent::__construct($this->addEncodedId($attributes));
    }

    /**
     * Set a encoded id to attributes
     *
     * @param array $attributes
     * @return array
     */
    protected function addEncodedId($attributes)
    {
        if (isset($attributes['id'])) {
            $attributes['encoded_id'] = jwt()->id($attributes['id']);
        }

        return $attributes;
    }

    /**
     * Add encoded id to appends property
     *
     * @return void
     */
    protected function appendEncodedId()
    {
        $this->appends = array_merge($this->appends, ['encoded_id']);
    }

    /**
     * Get a encoded id
     *
     * @return string
     */
    public function getEncodedIdAttribute()
    {
        return !empty($this->encoded_id) ? $this->encoded_id : (!empty($this->id) ? jwt()->id($this->id) : null);
    }

    /**
     * Check if value is a endoded id and decode it
     *
     * @param array $value
     * @return mixed
     */
    public static function resolveEncodedId($value)
    {
        if ($encodedId = Encrypter::jwtDecode($value)) {
            $value = $encodedId;
        }

        return $value;
    }

    /**
     * Find a model by its primary key
     *
     * @param mixed $id
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public static function find($id, $columns = ['*'])
    {
        $id = static::resolveEncodedId($id);

        if (is_array($id) || $id instanceof Arrayable) {
            return static::findMany($id, $columns);
        }

        return static::whereKey($id)->first($columns);
    }

    /**
     * Retrieve the model for a bound value
     *
     * @param mixed $value
     * @param string|null $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), static::resolveEncodedId($value))->first();
    }

    /**
     * Retrieve the model for a bound value
     *
     * @param mixed $value
     * @param string|null $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveSoftDeletableRouteBinding($value, $field = null)
    {
        $value = static::resolveEncodedId($value);

        return $this->where($field ?? $this->getRouteKeyName(), $value)->withTrashed()->first();
    }

    /**
     * Retrieve the child model for a bound value
     *
     * @param string $childType
     * @param mixed $value
     * @param string|null $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveChildRouteBinding($childType, $value, $field)
    {
        return $this->resolveChildRouteBindingQuery($childType, static::resolveEncodedId($value), $field)->first();
    }

    /**
     * Retrieve the child model for a bound value
     *
     * @param string $childType
     * @param mixed $value
     * @param string|null $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveSoftDeletableChildRouteBinding($childType, $value, $field)
    {
        $value = static::resolveEncodedId($value);

        return $this->resolveChildRouteBindingQuery($childType, $value, $field)->withTrashed()->first();
    }

    /**
     * Don't consult the query cache
     *
     * @return static
     */
    public static function withoutCache()
    {
        static::$withoutCache = true;
        return new static();
    }

    /**
     * Get a new query builder instance for the connection
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $conn = $this->getConnection();

        if (static::$withoutCache) {
            static::$withoutCache = false;
            return $conn->query();
        }

        $grammar = $conn->getQueryGrammar();

        return new CacheBuilder($conn, $grammar, $conn->getPostProcessor());
    }
}
