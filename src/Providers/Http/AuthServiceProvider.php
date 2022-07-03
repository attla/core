<?php

namespace Attla\Providers\Http;

use Attla\Auth\Guard;
use Attla\Auth\Authenticator;
use Attla\Auth\DefaultProvider;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider
     *
     * @return void
     */
    public function register()
    {
        $defaultProvider = new DefaultProvider($this->app);
        Guard::register('web', function () use ($defaultProvider) {
            return $defaultProvider;
        });
        Guard::register('api', function () use ($defaultProvider) {
            return $defaultProvider;
        });
    }

    /**
     * Bootstrap the application service
     *
     * @return void
     */
    public function boot()
    {
        $auth = new Authenticator($this->app);
        $this->app->instance('auth', $auth);
        $this->app['request']->setUserResolver(function ($guard = null) use ($auth) {
            return call_user_func($auth->userResolver(), $guard);
        });
    }
}
