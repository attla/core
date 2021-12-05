<?php

namespace Attla\Http;

use Illuminate\Contracts\Http\Kernel as KernelContract;

class Kernel implements KernelContract
{
    /**
     * The application's middleware stack
     *
     * @var string[]
     */
    public $middleware = [];

    /**
     * The application's route middleware groups
     *
     * @var array
     */
    public $middlewareGroups = [];

    /**
     * The application's route middleware
     *
     * @var array
     */
    public $routeMiddleware = [];

    /**
     * The priority-sorted list of middleware
     * Forces non-global middleware to always be in the given order
     *
     * @var string[]
     */
    protected $middlewarePriority = [
        \Attla\Middleware\SetTokens::class,
        \Attla\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ];

    /**
     * The application's service providers
     *
     * @var string[]
     */
    public $providers = [];

    /**
     * Determine if the kernel has a given middleware
     *
     * @param string $middleware
     * @return bool
     */
    public function hasMiddleware($middleware)
    {
        return in_array($middleware, $this->middleware);
    }

    /**
     * Add a new middleware to the beginning of the stack if it does not already exist.
     *
     * @param  string  $middleware
     * @return $this
     */
    public function prependMiddleware($middleware)
    {
        if (!$this->hasMiddleware($middleware)) {
            array_unshift($this->middleware, $middleware);
        }

        return $this;
    }

    /**
     * Add a new middleware to end of the stack if it does not already exist
     *
     * @param string $middleware
     * @return $this
     */
    public function pushMiddleware($middleware)
    {
        if (!$this->hasMiddleware($middleware)) {
            $this->middleware[] = $middleware;
        }

        return $this;
    }

    /**
     * Determine if the kernel has a given middleware group
     *
     * @param string $middleware
     * @return bool
     */
    public function hasMiddlewareGroup($middleware)
    {
        return in_array($middleware, $this->middlewareGroups);
    }

    /**
     * Prepend the given middleware to the given middleware group
     *
     * @param string $group
     * @param string $middleware
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function prependMiddlewareToGroup($group, $middleware)
    {
        if (! isset($this->middlewareGroups[$group])) {
            throw new \InvalidArgumentException("The [{$group}] middleware group has not been defined.");
        }

        if (!$this->hasMiddlewareGroup($middleware)) {
            array_unshift($this->middlewareGroups[$group], $middleware);
        }

        return $this;
    }

    /**
     * Append the given middleware to the given middleware group
     *
     * @param string $group
     * @param string $middleware
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function appendMiddlewareToGroup($group, $middleware)
    {
        if (! isset($this->middlewareGroups[$group])) {
            throw new \InvalidArgumentException("The [{$group}] middleware group has not been defined.");
        }

        if (!$this->hasMiddlewareGroup($middleware)) {
            $this->middlewareGroups[$group][] = $middleware;
        }

        return $this;
    }

    /**
     * Determine if the kernel has a given middleware priority
     *
     * @param string $middleware
     * @return bool
     */
    public function hasMiddlewarePriority($middleware)
    {
        return in_array($middleware, $this->middlewarePriority);
    }

    /**
     * Prepend the given middleware to the middleware priority list
     *
     * @param string $middleware
     * @return $this
     */
    public function prependToMiddlewarePriority($middleware)
    {
        if (!$this->hasMiddlewarePriority($middleware)) {
            array_unshift($this->middlewarePriority, $middleware);
        }

        return $this;
    }

    /**
     * Append the given middleware to the middleware priority list
     *
     * @param string $middleware
     * @return $this
     */
    public function appendToMiddlewarePriority($middleware)
    {
        if (!$this->hasMiddlewarePriority($middleware)) {
            $this->middlewarePriority[] = $middleware;
        }

        return $this;
    }

    /**
     * Get the priority-sorted list of middleware
     *
     * @return string[]
     */
    public function getMiddlewarePriority()
    {
        return $this->middlewarePriority;
    }

    /**
     * @inheritdoc
     */
    public function bootstrap()
    {
    }

    /**
     * @inheritdoc
     */
    public function handle($request)
    {
    }

    /**
     * @inheritdoc
     */
    public function terminate($request, $response)
    {
    }

    /**
     * @inheritdoc
     */
    public function getApplication()
    {
        return app();
    }
}
