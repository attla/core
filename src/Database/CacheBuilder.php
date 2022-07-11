<?php

namespace Attla\Database;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Query\Builder as QueryBuilder;

class CacheBuilder extends QueryBuilder
{
    /**
     * Run the query as a "select" statement against the connection
     *
     * @return array
     */
    protected function runSelect()
    {
        return Cache::store('array')->remember($this->getCacheKey(), 1, function () {
            return parent::runSelect();
        });
    }

    /**
     * Returns a unique string that can identify this query
     *
     * @return string
     */
    protected function getCacheKey()
    {
        return json_encode([
            $this->toSql() => $this->getBindings()
        ]);
    }
}
