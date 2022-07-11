<?php

namespace Attla\Providers\Http;

use Attla\Cookier;
use Illuminate\Support\ServiceProvider;

class CookierServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider
     *
     * @return void
     */
    public function register()
    {
        Cookier::setRequest($this->app['request']);
        Cookier::setPrefix(env('APP_PREFIX', ''));
    }
}
