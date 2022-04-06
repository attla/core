<?php

namespace Attla\Middleware;

use Fruitcake\Cors\CorsService;
use Illuminate\Container\Container;
use Illuminate\Http\Middleware\HandleCors;

class Cors
{
    /**
     * The container instance
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * The CORS service instance
     *
     * @var \Fruitcake\Cors\CorsService
     */
    protected $cors;

    /**
     * Create a new middleware instance
     *
     * @param \Illuminate\Container\Container $container
     * @param \Fruitcake\Cors\CorsService $cors
     * @return void
     */
    public function __construct(Container $container, CorsService $cors)
    {
        $this->container = $container;
        $this->cors = $cors;
    }

    /**
     * Handle an incoming request
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        return (new HandleCors($this->container, $this->cors))->handle($request, $next);
    }
}
