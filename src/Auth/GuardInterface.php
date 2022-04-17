<?php

namespace Attla\Auth;

use Illuminate\Contracts\Auth\Authenticatable;

interface GuardInterface
{
    /**
     * Attempt to authenticate a user using the given credentials
     *
     * @param array $credentials
     * @return mixed
     */
    public function attempt(array $credentials);

    /**
     * Log a user into the application without sessions or cookies
     *
     * @param array $credentials
     * @return bool
     */
    public function once(array $credentials = []);

    /**
     * Generate a token for a given user
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param int $remember
     * @return mixed
     */
    public function login(Authenticatable $user, int $remember = 30);

    /**
     * Log the given user ID into the application
     *
     * @param mixed $id
     * @param int $remember
     * @return mixed
     */
    public function loginUsingId($id, int $remember = 30);

    /**
     * Log the given user ID into the application without sessions or cookies
     *
     * @param mixed $id
     * @return mixed
     */
    public function onceUsingId($id);

    /**
     * Get the currently authenticated user
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function user();

    /**
     * Log the user out of the application
     *
     * @return void
     */
    public function logout();
}
