<?php

namespace Attla\Database;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Attla\Encrypter;
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
     * Find a model by its primary key
     *
     * @param mixed $id
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public static function find($id, $columns = ['*'])
    {
        if ($jwt = Encrypter::jwtDecode($id)) {
            $id = $jwt;
        }

        if (is_array($id) || $id instanceof Arrayable) {
            return static::findMany($id, $columns);
        }

        return static::whereKey($id)->first($columns);
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
