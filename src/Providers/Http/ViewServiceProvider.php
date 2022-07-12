<?php

namespace Attla\Providers\Http;

use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
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

        // flash
        $blade->directive('message', function ($expression) {
            return '<?php echo $__env->make(\'flash::message\', ' . ($expression ?: '[]') . ', '
                . '\Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>';
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
        })->filter()
        ->push(core_path('Flash/views'))
        ->all());
    }
}
