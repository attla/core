<?php

namespace Attla;

use Attla\PackageDiscover;
use Attla\Providers\{
    Repository,
    Console\ConsoleSupportServiceProvider,
    Http\HttpSupportServiceProvider,
};
use Illuminate\Contracts\Http\Kernel as ContractHttpKernel;
use Illuminate\Contracts\Console\Kernel as ContractConsoleKernel;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\{
    Routing\Pipeline,
    Container\Container,
    Filesystem\Filesystem,
    Events\EventServiceProvider,
    Log\LogServiceProvider,
    Routing\RoutingServiceProvider,
    Support\Arr,
    Support\Str,
    Support\Collection,
};
use Symfony\Component\Console\{
    Input\ArgvInput,
    Output\ConsoleOutput,
};
use Attla\Bootstrap\{
    LoadEnvironmentVariables,
    LoadConfiguration,
    HandleExceptions,
    RegisterFacades,
    SetRequest,
    RegisterProviders
};

class Application extends Container implements ApplicationContract
{
    /**
     * The Attla framework core package
     *
     * @var string
     */
    protected $corePackage = 'attla/core';

    /**
     * The bootstrap classes for the application
     *
     * @var string[]
     */
    protected $bootstrappers = [
        LoadEnvironmentVariables::class,
        LoadConfiguration::class,
        HandleExceptions::class,
        RegisterFacades::class,
        SetRequest::class,
        RegisterProviders::class,
    ];

    /**
     * The http application's service providers
     *
     * @var string[]|string
     */
    protected $httpProviders = HttpSupportServiceProvider::class;

    /**
     * The console application's service providers
     *
     * @var string[]|string
     */
    protected $consoleProviders = ConsoleSupportServiceProvider::class;

    /**
     * The application's aliases
     *
     * @var array
     */
    protected $coreAliases = [
        'app' => [self::class, \Illuminate\Container\Container::class, \Illuminate\Contracts\Container\Container::class, \Psr\Container\ContainerInterface::class],
        'blade.compiler' => [\Illuminate\View\Compilers\BladeCompiler::class],
        'cache' => [\Illuminate\Cache\CacheManager::class, \Illuminate\Contracts\Cache\Factory::class],
        'cache.store' => [\Illuminate\Cache\Repository::class, \Illuminate\Contracts\Cache\Repository::class, \Psr\SimpleCache\CacheInterface::class],
        'cache.psr6' => [\Symfony\Component\Cache\Adapter\Psr16Adapter::class, \Symfony\Component\Cache\Adapter\AdapterInterface::class, \Psr\Cache\CacheItemPoolInterface::class],
        'config' => [\Illuminate\Config\Repository::class, \Illuminate\Contracts\Config\Repository::class],
        'cookie' => [\Illuminate\Cookie\CookieJar::class, \Illuminate\Contracts\Cookie\Factory::class, \Illuminate\Contracts\Cookie\QueueingFactory::class],
        'db' => [\Illuminate\Database\DatabaseManager::class, \Illuminate\Database\ConnectionResolverInterface::class],
        'db.connection' => [\Illuminate\Database\Connection::class, \Illuminate\Database\ConnectionInterface::class],
        'db.schema' => [\Illuminate\Database\Schema\Builder::class],
        'events' => [\Illuminate\Events\Dispatcher::class, \Illuminate\Contracts\Events\Dispatcher::class],
        'files' => [\Illuminate\Filesystem\Filesystem::class],
        'filesystem' => [\Illuminate\Filesystem\FilesystemManager::class, \Illuminate\Contracts\Filesystem\Factory::class],
        'filesystem.disk' => [\Illuminate\Contracts\Filesystem\Filesystem::class],
        'filesystem.cloud' => [\Illuminate\Contracts\Filesystem\Cloud::class],
        'translator' => [\Illuminate\Translation\Translator::class, \Illuminate\Contracts\Translation\Translator::class],
        'log' => [\Illuminate\Log\LogManager::class, \Psr\Log\LoggerInterface::class],
        'mail.manager' => [\Illuminate\Mail\MailManager::class, \Illuminate\Contracts\Mail\Factory::class],
        'mailer' => [\Illuminate\Mail\Mailer::class, \Illuminate\Contracts\Mail\Mailer::class, \Illuminate\Contracts\Mail\MailQueue::class],
        'redirect' => [\Illuminate\Routing\Redirector::class],
        'request' => [\Illuminate\Http\Request::class, \Symfony\Component\HttpFoundation\Request::class],
        'router' => [\Illuminate\Routing\Router::class, \Illuminate\Contracts\Routing\Registrar::class, \Illuminate\Contracts\Routing\BindingRegistrar::class],
        'session' => [\Illuminate\Session\SessionManager::class],
        'session.store' => [\Illuminate\Session\Store::class, \Illuminate\Contracts\Session\Session::class],
        'url' => [\Illuminate\Routing\UrlGenerator::class, \Illuminate\Contracts\Routing\UrlGenerator::class],
        'validator' => [\Illuminate\Validation\Factory::class, \Illuminate\Contracts\Validation\Factory::class],
        'view' => [\Illuminate\View\Factory::class, \Illuminate\Contracts\View\Factory::class],
    ];

    /**
     * The application namespace
     *
     * @var string
     */
    protected $namespace;

    /**
     * The application version
     *
     * @var string
     */
    protected $version;

    /**
     * The custom environment path defined by the developer
     *
     * @var string
     */
    protected $environmentPath;

    /**
     * The environment file to load during bootstrapping
     *
     * @var string
     */
    protected $environmentFile = '.env';

    /**
     * Get the version of the application
     *
     * @return string
     */
    public function version()
    {
        if (!is_null($this->version)) {
            return $this->version;
        }

        if (is_file($instaledPackagesFile = $this->basePath('/vendor/composer/installed.json'))) {
            $installed = json_decode(file_get_contents($instaledPackagesFile), true);
            $packages = $installed['packages'] ?? $installed;

            foreach ($packages as $package) {
                if ($package['name'] == $this->corePackage) {
                    return $this->version = $package['version'];
                }
            }
        }

        throw new \RuntimeException('Unable to detect application version.');
    }

    /**
     * Get the current application locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this['config']->get('app.locale');
    }

    /**
     * Determine if the application locale is the given locale
     *
     * @param string $locale
     * @return bool
     */
    public function isLocale($locale)
    {
        return $this->getLocale() == $locale;
    }

    /**
     * Bootstrap the application's bindings ans aliases
     *
     * @return void
     */
    public function bootstrap()
    {
        $this->registerBaseBindings();
        $this->registerCoreAliases();
        $this->registerBaseServiceProviders();
        $this->bootstrapWith($this->bootstrappers);
        $this['provider']->boot();
    }

    /**
     * Register the basic bindings into the container
     *
     * @return void
     */
    protected function registerBaseBindings()
    {
        static::setInstance($this);
        $this['app'] = $this;
        $this[Container::class] = $this;
        $this['provider'] = new Repository($this);

        $this->singleton(
            ContractHttpKernel::class,
            \App\Http\Kernel::class
        );

        $this->singleton(
            ContractConsoleKernel::class,
            \App\Console\Kernel::class
        );

        $this->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \App\Exceptions\Handler::class
        );

        $this->singleton(PackageDiscover::class, function () {
            return new PackageDiscover(
                new Filesystem(),
                $this->basePath(),
                $this->packageServicesPath()
            );
        });
    }

    /**
     * Register all of the base service providers
     *
     * @return void
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(EventServiceProvider::class);
        $this->register(LogServiceProvider::class);
        $this->register(RoutingServiceProvider::class);
    }

    /**
     * Register the core class aliases in the container
     *
     * @return void
     */
    protected function registerCoreAliases()
    {
        foreach ($this->coreAliases as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }

    /**
     * Resolve service providers
     *
     * @param string[]|string $providers
     * @return void
     */
    public function register($providers, $force = false)
    {
        $this['provider']
            ->load($providers)
            ->register();
    }

    /**
     * Register environment service providers
     *
     * @return void
     */
    public function registerConfiguredProviders()
    {

        $providers = Collection::make($this->httpProviders)
                ->merge($this[
                    $this->runningInConsole() ? ContractConsoleKernel::class : ContractHttpKernel::class
                ]->providers);

        if ($this->runningInConsole()) {
            $providers = $providers->merge($this->consoleProviders);
        }

        $this->register(
            $providers->merge($this[PackageDiscover::class]->providers())
                ->merge($this['config']->get('app.providers', []))
                ->flatten()
                ->toArray()
        );
    }

    /**
     * Run the given array of bootstrap classes
     *
     * @param string[]|string $bootstrappers
     * @return void
     */
    public function bootstrapWith($bootstrappers)
    {
        foreach ((array) $bootstrappers as $bootstrapper) {
            $this[$bootstrapper]->bootstrap($this);
        }
    }

    /**
     * Sync the current state of the middleware to the router
     *
     * @param \Attla\Http\Kernel $kernel
     * @return void
     */
    protected function syncMiddlewaresToRouter(\Attla\Http\Kernel $kernel)
    {
        $router = $this['router'];
        $router->middlewarePriority = $kernel->getMiddlewarePriority();

        foreach ($kernel->middlewareGroups as $key => $middleware) {
            $router->middlewareGroup($key, $middleware);
        }

        foreach ($kernel->routeMiddleware as $key => $middleware) {
            $router->aliasMiddleware($key, $middleware);
        }
    }

    /**
     * Runs the application
     *
     * @return void
     */
    public function run()
    {
        $this->bindPathsInContainer();
        $this->bootstrap();

        if ($this->runningInConsole()) {
            return $this->runConsoleApplication();
        }

        $this->runHttpApplication();
    }

    /**
     * Runs the console application
     *
     * @return void
     */
    protected function runConsoleApplication()
    {
        return $this[ContractConsoleKernel::class]
            ->setName('Attla framework')
            ->handle(
                new ArgvInput(),
                new ConsoleOutput()
            );
    }

    /**
     * Runs the http application
     *
     * @return void
     */
    protected function runHttpApplication()
    {
        $router = $this['router'];
        $this->syncMiddlewaresToRouter($kernel = $this[ContractHttpKernel::class]);

        (new Pipeline($this))
            ->send($this['request'])
            ->through($kernel->middleware)
            ->then(function ($request) use ($router) {
                return $router->dispatch($request);
            })->send();
    }

    /**
     * Normalize a path
     *
     * @param string $path
     * @return string
     */
    public function normalizePath($path = '')
    {
        $doubleSeparator = DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR;
        $path = strtr($path, '\\/', $doubleSeparator);
        return preg_replace('#' . $doubleSeparator . '+#', DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Get the base path of the Attla installation
     *
     * @param string $path Optionally, a path to append to the base path
     * @return string
     */
    public function basePath($path = '')
    {
        return $this->normalizePath(realpath(getcwd()) . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Get the path to the application "app" directory
     *
     * @param string $path Optionally, a path to append to the app path
     * @return string
     */
    public function path($path = '')
    {
        return $this->basePath('app/' . $path);
    }

    /**
     * Get the path to the language files
     *
     * @param string $path Optionally, a path to append to the language path
     * @return string
     */
    public function langPath($path = '')
    {
        return $this->resourcePath('lang/' . $path);
    }

    /**
     * Set the language file directory.
     *
     * @param string $path
     * @return $this
     */
    public function useLangPath($path): self
    {
        $this['path.lang'] = $path;
        return $this;
    }

    /**
     * Get the path to the public / web directory
     *
     * @param string $path Optionally, a path to append to the public path
     * @return string
     */
    public function publicPath($path = '')
    {
        return $this->basePath('public/' . $path);
    }

    /**
     * Get the path to the storage directory
     *
     * @param string $path Optionally, a path to append to the storage path
     * @return string
     */
    public function storagePath($path = '')
    {
        return $this->basePath('storage/' . $path);
    }

    /**
     * Get the path to the database directory
     *
     * @param string $path Optionally, a path to append to the database path
     * @return string
     */
    public function databasePath($path = '')
    {
        return $this->basePath('database/' . $path);
    }

    /**
     * Get the path to the resources directory
     *
     * @param string $path Optionally, a path to append to the resource path
     * @return string
     */
    public function resourcePath($path = '')
    {
        return $this->basePath('resources/' . $path);
    }

    /**
     * Get the path to the config directory
     *
     * @param string $path Optionally, a path to append to the config path
     * @return string
     */
    public function configPath($path = '')
    {
        return $this->basePath('config/' . $path);
    }

    /**
     * Get the path to the packages directory
     *
     * @param string $path Optionally, a path to append to the package path
     * @return string
     */
    public function packagePath($path = '')
    {
        return $this->basePath('packages/' . $path);
    }

    /**
     * Get the packages services cache
     *
     * @return string
     */
    public function packageServicesPath()
    {
        return $this->packagePath('services.php');
    }

    /**
     * Bind all of the application paths in the container
     *
     * @return void
     */
    protected function bindPathsInContainer()
    {
        $this['path'] = $this->path();
        $this['path.base'] = $this->basePath();
        $this['path.config'] = $this->configPath();
        $this['path.public'] = $this->publicPath();
        $this['path.storage'] = $this->storagePath();
        $this['path.database'] = $this->databasePath();
        $this['path.resources'] = $this->resourcePath();

        $this->useLangPath(value(function () {
            if (is_dir($directory = $this->langPath())) {
                return $directory;
            }

            return $this->basePath('lang');
        }));
    }

    /**
     * Get the path to the environment file directory
     *
     * @return string
     */
    public function environmentPath(): string
    {
        return $this->environmentPath ?: $this->basePath();
    }

    /**
     * Set the directory for the environment file
     *
     * @param string $path
     * @return $this
     */
    public function useEnvironmentPath($path): self
    {
        $this->environmentPath = $path;
        return $this;
    }

    /**
     * Set the environment file to be loaded during bootstrapping
     *
     * @param string $file
     * @return $this
     */
    public function loadEnvironmentFrom(string $file): self
    {
        $this->environmentFile = $file;
        return $this;
    }

    /**
     * Get the environment file the application is using
     *
     * @return string
     */
    public function environmentFile(): string
    {
        return $this->environmentFile ?: '.env';
    }

    /**
     * Get the fully qualified path to the environment file
     *
     * @return string
     */
    public function environmentFilePath(): string
    {
        return $this->environmentPath() . DIRECTORY_SEPARATOR . $this->environmentFile();
    }

    /**
     * Get or check the current application environment
     *
     * @param string|array $environments
     * @return string|bool
     */
    public function environment(...$environments): bool
    {
        if (!$this->bound('env')) {
            return 'unknown';
        }

        if (count($environments) > 0) {
            return Str::is(Arr::flatten($environments), $this['env']);
        }

        return $this['env'];
    }

    /**
     * Determine if the application is in the local environment
     *
     * @return bool
     */
    public function isLocal(): bool
    {
        return $this->bound('env') && $this['env'] === 'local';
    }

    /**
     * Determine if the application is in the production environment
     *
     * @return bool
     */
    public function isProduction(): bool
    {
        return $this->bound('env') && $this['env'] === 'production';
    }

    /**
     * Detect the application's current environment
     *
     * @param \Closure $callback
     * @return string
     */
    public function detectEnvironment(\Closure $callback)
    {
        return $this['env'] = (new EnvironmentDetector())->detect($callback, $_SERVER['argv'] ?? null);
    }

    /**
     * Determine if the application is running in the console
     *
     * @return bool
     */
    public function runningInConsole(): bool
    {
        return in_array(\PHP_SAPI, ['cli', 'phpdbg']);
    }

    /**
     * Determine if the application is running unit tests
     *
     * @return bool
     */
    public function runningUnitTests(): bool
    {
        return $this->bound('env') && $this['env'] === 'testing';
    }

    /**
     * Determine if the application is running with debug mode enabled
     *
     * @return bool
     */
    public function hasDebugModeEnabled(): bool
    {
        return (bool) $this['config']->get('app.debug');
    }

    /**
     * Get the application namespace
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getNamespace()
    {
        if (!is_null($this->namespace)) {
            return $this->namespace;
        }

        $composer = json_decode(file_get_contents($this->basePath('composer.json')), true);

        foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (realpath($this->path()) === realpath($this->basePath($pathChoice))) {
                    return $this->namespace = $namespace;
                }
            }
        }

        throw new \RuntimeException('Unable to detect application namespace.');
    }

    public function bootstrapPath($path = '')
    {
    }

    public function isDownForMaintenance()
    {
    }

    public function maintenanceMode()
    {
    }

    public function registerDeferredProvider($provider, $service = null)
    {
    }

    public function resolveProvider($provider)
    {
    }

    public function boot()
    {
    }

    public function booting($callback)
    {
    }

    public function booted($callback)
    {
    }

    public function getProviders($provider)
    {
    }

    public function hasBeenBootstrapped()
    {
    }

    public function loadDeferredProviders()
    {
    }

    public function setLocale($locale)
    {
    }

    public function shouldSkipMiddleware()
    {
    }

    public function terminating($callback)
    {
    }

    public function terminate()
    {
    }
}
