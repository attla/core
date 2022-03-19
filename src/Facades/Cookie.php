<?php

namespace Attla\Facades;

/**
 * @see \Attla\Cookier
 */
class Cookie extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the registered name of the component
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Attla\Cookier::class;
    }
}
