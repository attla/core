<?php

namespace Attla\Auth;

use App\Models\User;
use Attla\Encrypter;
use Illuminate\Support\Str;
use Illuminate\Container\Container;

class Authenticator
{
    /**
     * @var \Illuminate\Container\Container
     */
    protected $app;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \App\Models\User
     */
    protected $user;

    /**
     * Column of user unique identifier
     *
     * @var string
     */
    protected $uniqueIdentifier;

    /**
     * The array of created "drivers".
     *
     * @var array
     */
    protected $guards = [
        'web' => 'getCookieFirstSign',
        'api' => 'getHeaderFirstSign',
    ];

    /**
     * The array of created "drivers".
     *
     * @var array
     */
    protected $guardUsed;

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
     * @param \Illuminate\Container\Container $app
     * @return void
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
        $this->request = $app['request'];
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
        $this->resolveGuard();
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
        if ($user = $this->user()) {
            return $user->id;
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
        return !is_null($this->user);
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
     * Log the user out of the application
     *
     * @return void
     */
    public function logout()
    {
        tokens()->delete('sign');
    }

    /**
     * Set the current user
     *
     * @param \App\Models\User $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        $user->exists = true;
        return $this;
    }

    /**
     * Validate a user's credentials
     *
     * @param array $credentials
     * @param int $remember
     * @param bool $returnSign
     * @return bool|string
     */
    public function validate(array $credentials = [], int $remember = 1800, bool $returnSign = false)
    {
        $credentials = $this->validateCredentials($credentials);

        if (!$credentials && !$credentials = $this->validateCredentials($this->getRequestCredentials())) {
            return false;
        }

        $user = $this->findUserByidentifier($credentials);

        if (!is_null($user) && $this->checkCredentials($user, $credentials)) {
            $this->setUser($user);
            $sign = $this->createSign($user, $remember);

            return $returnSign ? $sign : true;
        } else {
            return false;
        }
    }

    /**
     * Attempt to authenticate a user using the given credentials
     *
     * @param array $credentials
     * @param int $remember
     * @param bool $returnSign
     * @return bool
     */
    public function attempt(array $credentials = [], int $remember = 1800, bool $returnSign = false)
    {
        return $this->validate($credentials, $remember, $returnSign);
    }

    /**
     * Validate if credentials has required values
     *
     * @param array $credentials
     * @return array|false
     */
    protected function validateCredentials(array $credentials)
    {
        $uniqueIdentifier = $this->getUniqueIdentifier($credentials);

        if (!$uniqueIdentifier || empty($credentials['password'])) {
            return false;
        }

        return [
            $uniqueIdentifier => $credentials[$uniqueIdentifier],
            'password' => $credentials['password']
        ];
    }

    /**
     * Get credentials from request
     *
     * @return array
     */
    protected function getRequestCredentials()
    {
        return $this->app['request']->only('email', 'user', 'username', 'password');
    }

    /**
     * Get unique identifier of user from credentials
     *
     * @param array $credentials
     * @return string|false
     */
    protected function getUniqueIdentifier(array $credentials)
    {
        foreach (['email', 'username', 'user'] as $uniqueIdentifier) {
            if (array_key_exists($uniqueIdentifier, $credentials)) {
                return $this->uniqueIdentifier = $uniqueIdentifier;
            }
        }

        return false;
    }

    /**
     * Retrieve a user by the given credentials
     *
     * @param array $credentials
     * @return \App\Models\User|null
     */
    protected function findUserByidentifier(array $credentials)
    {
        if ($user = User::where($this->uniqueIdentifier, $credentials[$this->uniqueIdentifier])->first()) {
            return $user;
        }

        return null;
    }

    /**
     * Check if a credential password given as valid
     *
     * @param \App\Models\User $user
     * @param array $credentials
     * @return bool
     */
    protected function checkCredentials(User $user, array $credentials)
    {
        return Encrypter::hashEquals($credentials['password'], $user->password);
    }

    /**
     * Create a user sign
     *
     * @param \App\Models\User $user
     * @param int $remember
     * @return string
     */
    public function createSign(User $user, int $remember)
    {
        return tokens()->setSign('sign', $user->getAttributes(), $remember);
    }

    /**
     * Renew a user sign
     *
     * @param int $remember
     * @param bool $returnSign
     * @return \App\Models\User|string|false
     */
    public function renewSign(int $remember = 1800, bool $returnSign = false)
    {
        if ($user = User::find($this->id())) {
            $sign = $this->createSign($user, $remember);
            return $returnSign ? $sign : $user;
        }

        return false;
    }

    /**
     * Check a sign token on request
     *
     * @return void
     */
    protected function resolveGuard()
    {
        $user = null;
        $guard = $this->guards[$this->guardUsed];

        if (method_exists($this, $guard)) {
            $sign = $this->{$guard}();
        } elseif ($guard instanceof \Closure) {
            $sign = $guard($this->app);
        } else {
            abort("Auth driver [{$guard}] is not defined.");
        }

        if (is_object($sign)) {
            $user = new User((array) $sign);
            $user->exists = true;
        }

        $this->user = $user;
    }

    /**
     * Get the sign token from the request
     *
     * @return string
     */
    protected function getCookieFirstSign()
    {
        return tokens('sign') ?: $this->bearerToken();
    }

    /**
     * Get the sign token from the request
     *
     * @return string
     */
    protected function getHeaderFirstSign()
    {
        return $this->bearerToken() ?: tokens('sign');
    }

    /**
     * Get the bearer token from the request headers
     *
     * @return string
     */
    protected function bearerToken()
    {
        $token = $this->request->header('Authorization', '');

        if (Str::startsWith($token, 'Bearer ')) {
            $token = Str::substr($token, 7);
        }

        return $token;
    }
}
