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
     * Generate a token for a given user
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @return mixed
     */
    public function fromUser(Authenticatable $user);

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
