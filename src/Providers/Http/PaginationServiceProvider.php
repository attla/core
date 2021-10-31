<?php

namespace Attla\Providers\Http;

use Illuminate\Support\ServiceProvider;
use Attla\Pagination\PaginationState;

class PaginationServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider
     *
     * @return void
     */
    public function register()
    {
        PaginationState::resolveUsing($this->app);
    }
}
