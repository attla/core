<?php

namespace Attla\Auth;

use Attla\Jwt;
use Attla\Cookier;
use App\Models\User;
use Attla\Encrypter;
use Attla\Application;
use Illuminate\Contracts\Auth\Authenticatable;

class DefaultProvider implements GuardInterface
{
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
    protected $identifier = 'id';

    /**
     * Create a new authentication
     *
     * @param \Attla\Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->request = $app['request'];
    }

    /**
     * Attempt to authenticate a user using the given credentials
     *
     * @param array $credentials
     * @param int $remember
     * @param bool $returnSign
     * @return bool
     */
    public function attempt(array $credentials = [], int $remember = 30, bool $returnSign = false)
    {
        if (!is_null($user = $this->retrieveByCredentials($credentials))) {
            $sign = $this->login($user, $remember);

            return $returnSign ? $sign : true;
        }

        return false;
    }

    /**
     * Log a user into the application without sessions or cookies
     *
     * @param array $credentials
     * @return bool
     */
    public function once(array $credentials = [])
    {
        if (!is_null($user = $this->retrieveByCredentials($credentials))) {
            auth()->setUser($user);
            return true;
        }

        return false;
    }

    /**
     * Generate a token for a given user
     *
     * @param Authenticatable $user
     * @param int $remember
     * @return mixed
     */
    public function login(Authenticatable $user, int $remember = 30)
    {
        auth()->setUser($user);
        return $this->createSign($user, $remember);
    }

    /**
     * Log the given user ID into the application
     *
     * @param mixed $id
     * @param int $remember
     * @return mixed
     */
    public function loginUsingId($id, int $remember = 30, bool $returnSign = false)
    {
        if (!is_null($user = User::find($id))) {
            $sign = $this->login($user, $remember);

            return $returnSign ? $sign : true;
        }

        return false;
    }

    /**
     * Log the given user ID into the application without sessions or cookies
     *
     * @param mixed $id
     * @return mixed
     */
    public function onceUsingId($id)
    {
        if (!is_null($user = User::find($id))) {
            auth()->setUser($user);
            return true;
        }

        return false;
    }

    /**
     * Get the currently authenticated user
     *
     * @return \App\Models\User|null
     */
    public function user()
    {
        $user = null;

        if (is_object($sign = Cookier::get('sign') ?: Jwt::decode($this->request->bearerToken()))) {
            $user = new User((array) $sign);
            $user->exists = true;
        }

        return $this->user = $user;
    }

    /**
     * Log the user out of the application
     *
     * @return void
     */
    public function logout()
    {
        Cookier::forget('sign');
    }

    /**
     * Retrieve a user by the given credentials
     *
     * @param array $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (
            $this->checkCredentials($user = $this->findUserByIdentifier(
                $credentials = $this->validateCredentials($credentials)
                    ?: []
            ), $credentials)
        ) {
            return $user;
        }

        return null;
    }

    /**
     * Validate if credentials has required values
     *
     * @param array $credentials
     * @return array|false
     */
    protected function validateCredentials(array $credentials)
    {
        $identifier = $this->getIdentifier($credentials);

        if (!$identifier || empty($credentials['password'])) {
            return false;
        }

        return [
            $identifier => $credentials[$identifier],
            'password' => $credentials['password']
        ];
    }

    /**
     * Get unique identifier of user from credentials
     *
     * @param array $credentials
     * @return string|false
     */
    protected function getIdentifier(array $credentials)
    {
        foreach (['email', 'username', 'user'] as $identifier) {
            if (array_key_exists($identifier, $credentials)) {
                return $this->identifier = $identifier;
            }
        }

        return false;
    }

    /**
     * Retrieve a user by the given credentials
     *
     * @param array $credentials
     * @return Authenticatable|null
     */
    protected function findUserByIdentifier(array $credentials)
    {
        if (
            !empty($credentials[$this->identifier])
            && $user = User::where($this->identifier, $credentials[$this->identifier])->first()
        ) {
            return $user;
        }

        return null;
    }

    /**
     * Check if a credential password given as valid
     *
     * @param Authenticatable $user
     * @param array $credentials
     * @return bool
     */
    protected function checkCredentials(Authenticatable $user, array $credentials)
    {
        return Encrypter::hashEquals($credentials['password'], $user->password);
    }

    /**
     * Create a user sign
     *
     * @param Authenticatable $user
     * @param int $remember
     * @return string
     */
    public function createSign(Authenticatable $user, int $remember)
    {
        $sign = Jwt::payload($user->getAttributes())
            ->sign($remember)
            ->encode();
        Cookier::set('sign', $sign, $remember);
        return $sign;
    }

    /**
     * Renew a user sign
     *
     * @param int $remember
     * @param bool $returnSign
     * @return Authenticatable|string|false
     */
    public function renewSign(int $remember = 30, bool $returnSign = false)
    {
        if (!is_null($this->user) and $user = User::find($this->user->id)) {
            $sign = $this->createSign($user, $remember);
            return $returnSign ? $sign : $user;
        }

        return false;
    }
}
