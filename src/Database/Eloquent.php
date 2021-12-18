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

    public function __construct($attributes = [])
    {
        Encapsulator::getInstance();

        $this->appendEncodedId();

        if (!is_array($attributes)) {
            $attributes = (array) $attributes;
        }

        parent::__construct($attributes);
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
     * Set a given attribute on the model
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        $value = static::resolveEncodedId($value);

        // First we will check for the presence of a mutator for the set operation
        // which simply lets the developers tweak the attribute as it is set on
        // this model, such as "json_encoding" a listing of data for storage.
        if ($this->hasSetMutator($key)) {
            return $this->setMutatedAttributeValue($key, $value);
        }

        // If an attribute is listed as a "date", we'll convert it from a DateTime
        // instance into a form proper for storage on the database tables using
        // the connection grammar's date format. We will auto set the values.
        elseif ($value && $this->isDateAttribute($key)) {
            $value = $this->fromDateTime($value);
        }

        if ($this->isEnumCastable($key)) {
            $this->setEnumCastableAttribute($key, $value);
            return $this;
        }

        if ($this->isClassCastable($key)) {
            $this->setClassCastableAttribute($key, $value);
            return $this;
        }

        if (! is_null($value) && $this->isJsonCastable($key)) {
            $value = $this->castAttributeAsJson($key, $value);
        }

        // If this attribute contains a JSON ->, we'll set the proper value in the
        // attribute's underlying array. This takes care of properly nesting an
        // attribute in the array's value in the case of deeply nested items.
        if (Str::contains($key, '->')) {
            return $this->fillJsonAttribute($key, $value);
        }

        if (!is_null($value) && $this->isEncryptedCastable($key)) {
            $value = $this->castAttributeAsEncryptedString($key, $value);
        }

        $this->attributes[$key] = $value;

        return $this;
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
