<?php

namespace Attla\Bootstrap;

use Attla\Application;

class RegisterProviders
{
    /**
     * Register the application service providers
     *
     * @param \Attla\Application $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $app->registerApplicationProviders();
        $app->registerPackagesProviders();
    }
}
