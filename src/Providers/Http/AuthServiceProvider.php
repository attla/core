<?php

namespace Attla\Providers\Http;

use Illuminate\Support\ServiceProvider;
use Attla\Auth\Authenticator;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider
     *
     * @return void
     */
    public function register()
    {
        $auth = new Authenticator($this->app);
        $this->app->instance('auth', $auth);
        $this->app['request']->setUserResolver(function ($guard = null) use ($auth) {
            return call_user_func($auth->userResolver(), $guard);
        });
    }
}
