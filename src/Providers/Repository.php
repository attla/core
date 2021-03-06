<?php

namespace Attla\Providers;

use Attla\Application;
use Illuminate\Support\ServiceProvider;

class Repository extends ServiceProvider
{
    /**
     * The application container
     *
     * @var \Attla\Application
     */
    protected $app;

    /**
     * The providers to regirster and boot
     *
     * @var array
     */
    protected $queue = [];

    /**
     * The registered providers of the application
     *
     * @var array
     */
    protected $registered = [];

    /**
     * The instances of registered providers of the application
     *
     * @var array
     */
    protected $instances = [];

    /**
     * The booted providers of the application
     *
     * @var array
     */
    protected $booted = [];

    /**
     * Create a new provider instance
     *
     * @param \Attla\Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Register the application service providers
     *
     * @return $this
     */
    public function register()
    {
        foreach ($this->queue as $provider) {
            if (!$this->isRegistered($provider)) {
                $provider = $this->resolveProvider($provider);
                $this->markAsRegistered($provider);

                $provider->register();

                // If there are bindings / singletons set as properties on the provider we
                // will spin through them and register them with the application, which
                // serves as a convenience layer while registering a lot of bindings
                if (property_exists($provider, 'bindings')) {
                    foreach ($provider->bindings as $key => $value) {
                        $this->app->bind($key, $value);
                    }
                }

                if (property_exists($provider, 'singletons')) {
                    foreach ($provider->singletons as $key => $value) {
                        $this->app->singleton($key, $value);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Resolve a service provider instance from the class name
     *
     * @param string $provider
     * @return \Illuminate\Support\ServiceProvider
     */
    protected function resolveProvider($provider)
    {
        return new $provider($this->app);
    }

    /**
     * Mark the given provider as registered
     *
     * @param \Illuminate\Support\ServiceProvider $provider
     * @return void
     */
    protected function markAsRegistered($provider)
    {
        $name = get_class($provider);
        $this->instances[$name] = $provider;
        $this->registered[] = $name;
    }

    /**
     * Boot the application service providers
     *
     * @return $this
     */
    public function boot()
    {
        foreach ($this->instances as $provider) {
            $name = $this->name($provider);
            if (!$this->isBooted($name)) {
                $provider->callBootingCallbacks();

                if (method_exists($provider, 'boot')) {
                    $provider->boot();
                }

                $provider->callBootedCallbacks();
                $this->booted[] = $name;
            }
        }

        return $this;
    }

    /**
     * Resolve service name
     *
     * @param \Illuminate\Support\ServiceProvider|string $provider
     * @return string
     */
    protected function name($provider)
    {
        return is_string($provider) ? $provider : get_class($provider);
    }

    /**
     * Check if the service providers are registered
     *
     * @param string $provider
     * @return bool
     */
    protected function isRegistered($provider)
    {
        return in_array($provider, $this->registered) && isset($this->instances[$provider]);
    }

    /**
     * Check if the service providers are booted
     *
     * @param string $provider
     * @return bool
     */
    protected function isBooted($provider)
    {
        return in_array($provider, $this->booted);
    }

    /**
     * Load new service providers
     *
     * @param string[]|string $providers
     * @return $this
     */
    public function load($providers)
    {
        $this->queue = array_merge($this->queue, (array) $providers);
        return $this;
    }
}
