<?php

namespace Attla\Facades;

/**
 * @see \Attla\Encrypter
 */
class Encrypter extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the registered name of the component
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Attla\Encrypter::class;
    }
}
