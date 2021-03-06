<?php

namespace Attla\Bootstrap;

use Attla\Application;
use Attla\AliasLoader;
use Attla\PackageDiscover;
use Illuminate\Support\Facades\Facade;

class RegisterFacades
{
    private $aliases = [
    ];

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

        AliasLoader::getInstance(
            Facade::defaultAliases()
                ->merge($app['config']->get('app.aliases', []))
                ->merge($app[PackageDiscover::class]->aliases())
                ->merge($this->aliases)
                ->toArray()
        )->register();
    }
}
