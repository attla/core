<?php

namespace Attla\Providers\Http;

use Attla\Cookier;
use Attla\Encrypter;
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
        $config = $this->app['config'];
        Cookier::setRequest($this->app['request']);
        Cookier::setPrefix($config->get('prefix', ''));

        $config->set('csrf', Encrypter::hash(url()->full() . browser() . substr(browser_version(), 0, 2)));
    }
}
