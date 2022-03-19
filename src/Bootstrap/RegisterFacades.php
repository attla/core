<?php

namespace Attla\Bootstrap;

use Attla\Application;
use Attla\AliasLoader;
use Attla\PackageDiscover;
use Illuminate\Support\Facades\Facade;
use Attla\Facades\{
    Cookie,
    Encrypter,
    Jwt,
};

class RegisterFacades
{
    private $aliases = [
        'Cookie' => Cookie::class,
        'Encrypter' => Encrypter::class,
        'Jwt' => Jwt::class,
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
                ->merge($app['config']->get('aliases', []))
                ->merge($app[PackageDiscover::class]->aliases())
                ->merge($this->aliases)
                ->toArray()
        )->register();
    }
}
