<?php

namespace Attla\Database;

use Attla\Encrypter;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Contracts\Support\Arrayable;

abstract class Eloquent extends EloquentModel
{
    /**
     * Stores whether not to use query cache
     *
     * @var bool
     */
    protected static $withoutCache = false;

    /**
     * Create a new Eloquent model instance
     *
     * @param array $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        Encapsulator::getInstance();
        parent::__construct($attributes);
    }

    /**
     * Get a encoded id
     *
     * @return string
     */
    public function getEncodedIdAttribute()
    {
        $key = $this->getKeyName();
        return !empty($this->{$key}) ? jwt()->id($this->{$key}) : null;
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
            return $encodedId;
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
        return parent::find(static::resolveEncodedId($id), $columns);
    }

    /**
     * Create a new Eloquent query builder for the model
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * Set a given attribute on the model
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        return parent::setAttribute($key, static::resolveEncodedId($value));
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
        return parent::resolveRouteBinding(static::resolveEncodedId($value), $field);
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
        return parent::resolveSoftDeletableRouteBinding(static::resolveEncodedId($value), $field);
    }

    /**
     * Retrieve the child model for a bound value
     *
     * @param string $childType
     * @param mixed $value
     * @param string|null $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveChildRouteBinding($childType, $value, $field = null)
    {
        return parent::resolveChildRouteBinding($childType, static::resolveEncodedId($value), $field);
    }

    /**
     * Retrieve the child model for a bound value
     *
     * @param string $childType
     * @param mixed $value
     * @param string|null $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveSoftDeletableChildRouteBinding($childType, $value, $field = null)
    {
        return parent::resolveSoftDeletableChildRouteBinding($childType, static::resolveEncodedId($value), $field);
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
