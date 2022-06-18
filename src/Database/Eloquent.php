<?php

namespace Attla\Database;

use Illuminate\Support\Enumerable;
use Illuminate\Contracts\Support\Jsonable;
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

    /**
     * Create a new Eloquent model instance
     *
     * @param array|object $attributes
     * @return void
     */
    public function __construct($attributes = [])
    {
        Encapsulator::getInstance();

        if ($attributes instanceof Enumerable) {
            $attributes = $attributes->all();
        } elseif ($attributes instanceof Arrayable) {
            $attributes = $attributes->toArray();
        } elseif ($attributes instanceof Jsonable) {
            $attributes = json_decode($value->toJson(), true);
        } elseif ($attributes instanceof \JsonSerializable) {
            $attributes = (array) $attributes->jsonSerialize();
        } elseif ($attributes instanceof \Traversable) {
            $attributes = iterator_to_array($attributes);
        } elseif (!is_array($attributes)) {
            $attributes = (array) $attributes;
        }

        parent::__construct($attributes);
    }

    /**
     * Get a encoded id
     *
     * @return string
     */
    public function getEncodedIdAttribute()
    {
        return EncodedId::generate($this);
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
        return parent::setAttribute($key, EncodedId::resolver($value));
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
        return parent::resolveRouteBinding(EncodedId::resolver($value), $field);
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
        return parent::resolveSoftDeletableRouteBinding(EncodedId::resolver($value), $field);
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
        return parent::resolveChildRouteBinding($childType, EncodedId::resolver($value), $field);
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
        return parent::resolveSoftDeletableChildRouteBinding($childType, EncodedId::resolver($value), $field);
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
