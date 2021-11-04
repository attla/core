<?php

namespace Attla\Exceptions;

use Illuminate\Support\Facades\View;

class RegisterErrorViewPaths
{
    /**
     * Register the error view paths
     *
     * @return void
     */
    public function __invoke()
    {
        View::replaceNamespace('errors', collect(config('view.paths'))->map(function ($path) {
            if (is_dir($errorsPath = "$path/errors")) {
                return $errorsPath;
            }
        })->filter()
        ->push(__DIR__ . '/views')
        ->all());
    }
}
