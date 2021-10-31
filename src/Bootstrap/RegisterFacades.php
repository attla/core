<?php

namespace Attla\Bootstrap;

use Attla\Application;
use Illuminate\Support\Facades\Facade;

class RegisterFacades
{
    /**
     * Bootstrap the given application
     *
     * @param \Attla\Application $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);
    }
}
