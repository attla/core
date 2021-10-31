<?php

namespace Attla\Database;

use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

class Pivot extends Eloquent
{
    use AsPivot;

    /**
     * Indicates if the IDs are auto-incrementing
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that aren't mass assignable
     *
     * @var array
     */
    protected $guarded = [];
}
