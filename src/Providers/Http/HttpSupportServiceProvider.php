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
        \Illuminate\Database\DatabaseServiceProvider::class,
        \Illuminate\Filesystem\FilesystemServiceProvider::class,
        \Illuminate\Routing\RoutingServiceProvider::class,
        \Illuminate\Mail\MailServiceProvider::class,
        SessionServiceProvider::class,
        \Illuminate\Translation\TranslationServiceProvider::class,
        ValidationServiceProvider::class,
        \Illuminate\View\ViewServiceProvider::class,
        ViewServiceProvider::class,
        \Illuminate\Pagination\PaginationServiceProvider::class,
        \App\Providers\RouteServiceProvider::class,
        AuthServiceProvider::class,
        IgnitionServiceProvider::class,
    ];
}
