<?php

namespace Attla\Providers;

use Illuminate\Support\ServiceProvider;

class Aggregate extends ServiceProvider
{
    /**
     * The provider class names
     *
     * @var array
     */
    protected $providers = [];

    /**
     * Register the service provider
     *
     * @return void
     */
    public function register()
    {
        $this->app->register($this->providers);
    }
}
