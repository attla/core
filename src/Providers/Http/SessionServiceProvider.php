<?php

namespace Attla\Providers\Http;

use Illuminate\Support\ServiceProvider;
use Illuminate\Session\SessionManager;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider
     *
     * @return void
     */
    public function register()
    {
        $this->registerSessionManager();
        $this->registerSessionDriver();
        $this->registerSessionRequest();
    }

    /**
     * Register the session manager instance
     *
     * @return void
     */
    protected function registerSessionManager()
    {
        $this->app->singleton('session', function ($app) {
            $sessionManager = new SessionManager($app);
            $sessionManager->setRequestOnHandler($app['request']);
            return $sessionManager;
        });
    }

    /**
     * Register the session driver instance
     *
     * @return void
     */
    protected function registerSessionDriver()
    {
        $this->app->singleton('session.store', function ($app) {
            return $app['session']->driver();
        });
    }

    /**
     * Register the session driver on request instance
     *
     * @return void
     */
    protected function registerSessionRequest()
    {
        $this->app['request']->setLaravelSession($this->app['session.store']);
    }
}
