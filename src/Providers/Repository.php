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
                $this->registered[] = $provider;
                $instance = $this->createProvider($provider);
                $this->instances[$provider] = $instance;
                $instance->register();
            }
        }

        return $this;
    }

    /**
     * Boot the application service providers
     *
     * @return $this
     */
    public function boot()
    {
        foreach ($this->queue as $provider) {
            if ($this->isRegistered($provider) && !$this->isBooted($provider)) {
                $this->booted[] = $provider;
                $instance = $this->instances[$provider];

                $instance->callBootingCallbacks();

                if (method_exists($instance, 'boot')) {
                    // $this->app->call([$instance, 'boot']);
                    $instance->boot();
                }

                $instance->callBootedCallbacks();
            }
        }

        return $this;
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
     * Create a new provider instance
     *
     * @param string $provider
     * @return \Illuminate\Support\ServiceProvider
     */
    protected function createProvider($provider)
    {
        return new $provider($this->app);
    }

    /**
     * Load new service providers
     *
     * @param array|string $providers
     * @return $this
     */
    public function load($providers)
    {
        $this->queue = array_merge($this->queue, (array) $providers);
        return $this;
    }
}
