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
        if ($encodedId = Encrypter::jwtDecode($value)) {
            $value = $encodedId;
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
        if ($column instanceof \Closure && is_null($operator)) {
            $column($query = $this->model->newQueryWithoutRelationships());

            $this->query->addNestedWhereQuery($query->getQuery(), $boolean);
        } else {
            $args = func_get_args();
            array_walk_recursive($args, function (&$value) {
                $value = $this->resolveEncodedId($value);
            });
            $this->query->where(...$args);
        }

        return $this;
    }

    /**
     * Update records in the database
     *
     * @param array $values
     * @return int
     */
    public function update(array $values)
    {
        return $this->toBase()->update($this->addUpdatedAtColumn(array_map(function ($value) {
            return $this->resolveEncodedId($value);
        }, $values)));
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
        if (empty($values)) {
            return 0;
        }

        if (! is_array(reset($values))) {
            $values = [$values];
        }

        if (is_null($update)) {
            $update = array_keys(reset($values));
        }

        return $this->toBase()->upsert(
            $this->addTimestampsToUpsertValues(array_map(function ($value) {
                return $this->resolveEncodedId($value);
            }, $values)),
            $uniqueBy,
            $this->addUpdatedAtToUpsertColumns(array_map(function ($value) {
                return $this->resolveEncodedId($value);
            }, $update))
        );
    }
}
