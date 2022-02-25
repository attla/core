<?php

namespace Attla\Database;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class Builder extends EloquentBuilder
{
    /**
     * Find a model by its primary key
     *
     * @param mixed $id
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function find($id, $columns = ['*'])
    {
        return parent::find(EncodedId::resolver($id), $columns);
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
        return parent::where(...EncodedId::resolver(func_get_args()));
    }

    /**
     * Update records in the database
     *
     * @param array $values
     * @return int
     */
    public function update(array $values)
    {
        return parent::update(EncodedId::resolver($values));
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
            EncodedId::resolver($values),
            $uniqueBy,
            EncodedId::resolver($update),
        );
    }
}
