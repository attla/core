<?php

namespace Attla\Providers\Http;

use Attla\Tokens;
use Attla\Encrypter;
use Illuminate\Support\ServiceProvider;

class TokensServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('tokens', function ($app) {
            $tokens = new Tokens();
            $request = $app['request'];

            foreach ($request->cookies->all() as $key => $value) {
                $tokens->{$key} = $value;
            }

            $token = Encrypter::hash(url()->full() . browser() . substr(browser_version(), 0, 2));
            $tokens->csrf = $token;

            return $tokens;
        });
    }
}
