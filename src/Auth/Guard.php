<?php

namespace Attla\Auth;

use Attla\Application;
use Attla\Auth\GuardInterface;

class Guard
{
    protected static $guards = [];

    /**
     * Register a new guard
     *
     * @return void
     */
    public static function register($name, \Closure $callback)
    {
        static::$guards[$name] = $callback;
    }

    /**
     * Attempt to get the guard from the local cache.
     *
     * @param string|null $name
     * @param \Attla\Application $app
     * @return GuardInterface
     *
     * @throws \InvalidArgumentException
     */
    protected static function resolve($name, Application $app): GuardInterface
    {
        if (isset(static::$guards[$name])) {
            $guard = static::$guards[$name];

            if ($guard instanceof GuardInterface) {
                return $guard;
            }

            return static::$guards[$name] = $app->instance($name, $guard($app));
        }

        throw new \InvalidArgumentException("Auth guard [{$name}] is not defined.");
    }
}
