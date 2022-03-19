<?php

namespace Attla\Providers\Http;

use Attla\Providers\Aggregate;

class HttpSupportServiceProvider extends Aggregate
{
    /**
     * The provider class names
     *
     * @var string[]
     */
    protected $providers = [
        \Illuminate\Cache\CacheServiceProvider::class,
        \Illuminate\Cookie\CookieServiceProvider::class,
        CookierServiceProvider::class,
        \Illuminate\Database\DatabaseServiceProvider::class,
        \Illuminate\Filesystem\FilesystemServiceProvider::class,
        \Illuminate\Routing\RoutingServiceProvider::class,
        SessionServiceProvider::class,
        \Illuminate\Translation\TranslationServiceProvider::class,
        ValidationServiceProvider::class,
        \Illuminate\View\ViewServiceProvider::class,
        ViewServiceProvider::class,
        PaginationServiceProvider::class,
        \App\Providers\RouteServiceProvider::class,
        AuthServiceProvider::class,
        IgnitionServiceProvider::class,
    ];
}
