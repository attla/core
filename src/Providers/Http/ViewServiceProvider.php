<?php

namespace Attla\Providers\Http;

use Attla\Minify;
use Attla\Application;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Replacement namespaces
     *
     * @var array
     */
    protected $namespaces = [
        'Route::' => 'Illuminate\Support\Facades\Route::',
        'Arr::' => 'Illuminate\Support\Arr::',
    ];

    /**
     * Register the service provider
     *
     * @return void
     */
    public function register()
    {
        $bladeCompiler = $this->app['blade.compiler'];

        $this->registerNamespaces();
        $this->defineDirectives($bladeCompiler);

        if ($this->app['config']['minify']) {
            $bladeCompiler->extend(function ($value, $compiler) {
                return Minify::compile($this->replaceNamespaces($value), [
                    'disable_comments' => true,
                    'preserve_conditional_comments' => true,
                ]);
            });
        }
    }

    /**
     * Define custom blade directives
     *
     * @param \Illuminate\View\Compilers\BladeCompiler $blade
     * @return void
     */
    private function defineDirectives($blade)
    {
        // route
        $blade->directive('route', function ($expression) {
            return "<?php echo route($expression); ?>";
        });

        // dd
        $blade->directive('dd', function ($expression) {
            return "<?php dumper($expression); ?>";
        });

        // url
        $blade->directive('url', function ($expression) {
            return "<?php echo url($expression); ?>";
        });

        $blade->directive('uri', function ($expression) {
            return "<?php echo url($expression); ?>";
        });

        // asset
        $blade->directive('asset', function ($expression) {
            return "<?php echo asset($expression); ?>";
        });
    }

    /**
     * Replace laravel namespaces
     *
     * @param string $html
     * @return string
     */
    private function replaceNamespaces($html)
    {
        return str_replace(array_keys($this->namespaces), array_values($this->namespaces), $html);
    }

    /**
     * Set Attla view namespaces
     *
     * @return void
     */
    private function registerNamespaces()
    {
        //flashs
        $this->app['view']->addNamespace('flash', collect($this->app['config']['view.paths'])->map(function ($path) {
            if (is_dir($flashPath = "$path/flash")) {
                return $flashPath;
            }
        })->filter(function ($path) {
            return $path;
        })->push(core_path('Flash/views'))->all());
    }
}
