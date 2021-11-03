<?php

namespace Attla\Auth;

use Attla\Application;
use Attla\Auth\GuardInterface;
use Illuminate\Contracts\Auth\Authenticatable;

class Authenticator extends Guard
{
    /**
     * @var \Illuminate\Container\Container
     */
    protected $app;

    /**
     * The last guard used
     *
     * @var array
     */
    protected $guardUsed;

    /**
     * @var Authenticatable
     */
    protected $user;

    /**
     * The user resolver shared by various services
     * Determines the default user for Gate and Request
     *
     * @var \Closure
     */
    protected $userResolver;

    /**
     * Create a new authentication
     *
     * @param \Attla\Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->guard();

        $this->userResolver = function ($name = null) {
            return $this->guard($name)->user();
        };
    }

    /**
     * Attempt to get the guard from the local cache.
     *
     * @param string|null $name
     * @return $this
     */
    public function guard($name = null)
    {
        $this->guardUsed = $name ?: $this->getDefaultDriver();
        $this->user = $this->resolveGuard()->user();
        return $this;
    }

    /**
     * Set the default guard driver the factory should serve
     *
     * @param string $name
     * @return void
     */
    public function shouldUse($name)
    {
        $this->setDefaultDriver($name ?: $this->getDefaultDriver());
    }

    /**
     * Get the default authentication driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['auth.guard'];
    }

    /**
     * Set the default authentication driver name.
     *
     * @param string $name
     * @return void
     */
    public function setDefaultDriver($name)
    {
        $this->app['config']['auth.guard'] = $name;
    }

    /**
     * Get the user resolver callback
     *
     * @return \Closure
     */
    public function userResolver()
    {
        return $this->userResolver;
    }

    /**
     * Get the currently authenticated user
     *
     * @return \App\Models\User|null
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * Get the ID for the currently authenticated user
     *
     * @return mixed|null
     */
    public function id()
    {
        if ($this->check()) {
            return optional($this->user)->id;
        }

        return null;
    }

    /**
     * Determine if the current user is authenticated
     *
     * @return bool
     */
    public function check()
    {
        return $this->user instanceof Authenticatable;
    }

    /**
     * Determine if the current user is a guest
     *
     * @return bool
     */
    public function guest()
    {
        return !$this->check();
    }

    /**
     * Set the current user
     *
     * @param Authenticatable $user
     * @return $this
     */
    public function setUser(Authenticatable $user)
    {
        $this->user = $user;
        $user->exists = true;
        return $this;
    }

    /**
     * Resolve guard instance
     *
     * @return GuardInterface
     */
    protected function resolveGuard(): GuardInterface
    {
        return static::resolve($this->guardUsed, $this->app);
    }

    /**
     * Dynamically call the default guard driver instance
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->resolveGuard()->{$method}(...$parameters);
        return static::resolve($this->guardUsed)->{$method}(...$parameters);
    }
}
