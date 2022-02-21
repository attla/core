<?php

namespace Attla\Database;

use Attla\Encrypter;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class Builder extends EloquentBuilder
{
    /**
     * Check if value is a endoded id and decode it
     *
     * @param array $value
     * @return mixed
     */
    public function resolveEncodedId($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'resolveEncodedId'], $value);
        }

        if (is_string($value) and $encodedId = Encrypter::jwtDecode($value)) {
            return $encodedId;
        }

        return $value;
    }

    /**
     * Add a basic where clause to the query
     *
     * @param \Closure|string|array|\Illuminate\Database\Query\Expression $column
     * @param mixed $operator
     * @param mixed $value
     * @param string $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        return parent::where(...$this->resolveEncodedId(func_get_args()));
    }

    /**
     * Update records in the database
     *
     * @param array $values
     * @return int
     */
    public function update(array $values)
    {
        return parent::update($this->resolveEncodedId($values));
    }

    /**
     * Insert new records or update the existing ones
     *
     * @param array $values
     * @param array|string $uniqueBy
     * @param array|null $update
     * @return int
     */
    public function upsert(array $values, $uniqueBy, $update = null)
    {
        return parent::upsert(
            $this->resolveEncodedId($values),
            $uniqueBy,
            $this->resolveEncodedId($update),
        );
    }
}
