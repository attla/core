<?php

namespace Attla;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class Cookier extends \ArrayObject
{
    /**
     * Request instance
     *
     * @var \Illuminate\Http\Request
     */
    protected static $request;

    /**
     * Cookie prefix name
     *
     * @var string
     */
    protected static $prefix = '';

    /**
     * Set the request instance
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public static function setRequest(Request $request)
    {
        static::$request = $request;
    }

    /**
     * Add the prefix if there is none
     *
     * @param string $key
     * @return string
     */
    public static function withPrefix(string $name)
    {
        $name = Str::slug($name, '_');

        if (!Str::startsWith($name, static::$prefix)) {
            $name = static::$prefix . $name;
        }

        return $name;
    }

    /**
     * Set token prefix
     *
     * @param string $prefix
     * @return void
     */
    public static function setPrefix(string $prefix)
    {
        static::$prefix = $prefix;
    }

    /**
     * Get the prefix
     *
     * @return string
     */
    public static function prefix()
    {
        return static::$prefix;
    }

    /**
     * Determine if a cookie exists on the request
     *
     * @param string $name
     * @return bool
     */
    public static function has($name)
    {
        return !is_null(static::$request->cookie(static::withPrefix($name), null));
    }

    /**
     * Alias for has
     *
     * @param string $name
     * @return void
     */
    public static function exists($name)
    {
        return static::has($name);
    }

    /**
     * Retrieve a cookie from the request
     *
     * @param array|string|null $name
     * @param mixed $default
     * @return string|array|null
     */
    public static function get($name = null, $default = null)
    {
        if (is_array($name)) {
            return static::getMany($name);
        }

        if (is_null($name)) {
            return static::all();
        }

        return static::value(static::$request->cookie(static::withPrefix($name), $default));
    }

    /**
     * Retrieve many cookies
     *
     * @param array $keys
     * @return array
     */
    public static function getMany($keys)
    {
        $cookies = [];

        foreach ($keys as $name => $default) {
            if (is_numeric($name)) {
                [$name, $default] = [$default, null];
            }

            $cookies[$name] = static::get($name, $default);
        }

        return $cookies;
    }

    /**
     * Retrieve all cookies
     *
     * @return array
     */
    public static function all()
    {
        $cookies = static::$request->cookies->all();

        return array_combine(
            array_map(
                fn($value) => ltrim($value, static::$prefix),
                array_keys($cookies)
            ),
            array_map(
                fn($value) => static::value($value),
                $cookies
            )
        );
    }

    /**
     * Resolve a cookie value
     *
     * @param mixed $value
     * @return mixed
     */
    public static function value($value)
    {
        if ($jwtDecoded = Jwt::decode($value)) {
            return $jwtDecoded;
        }

        return $value;
    }

    /**
     * Set a cookie value
     *
     * @param string $name
     * @param string $value
     * @param int $minutes
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null $secure
     * @param bool $httpOnly
     * @param bool $raw
     * @param string|null $sameSite
     * @return \Illuminate\Cookie\CookieJar|\Symfony\Component\HttpFoundation\Cookie
     */
    public static function set(
        $name,
        $value,
        $minutes = 30,
        $path = null,
        $domain = null,
        $secure = null,
        $httpOnly = true,
        $raw = false,
        $sameSite = null
    ) {
        $name = static::withPrefix($name);
        static::$request->cookies->set($name, $value);

        return Cookie::queue(
            $name,
            $value,
            $minutes,
            $path,
            $domain,
            $secure,
            $httpOnly,
            $raw,
            $sameSite
        );
    }

    /**
     * Alias for set
     *
     * @param string $key
     * @param string $value
     * @param integer $ttl
     * @return void
     */
    public static function store(
        $name,
        $value,
        $minutes = 30,
        $path = null,
        $domain = null,
        $secure = null,
        $httpOnly = true,
        $raw = false,
        $sameSite = null
    ) {
        return static::set(
            $name,
            $value,
            $minutes,
            $path,
            $domain,
            $secure,
            $httpOnly,
            $raw,
            $sameSite
        );
    }

    /**
     * Forget a cookie by name
     *
     * @param string $name
     * @return void
     */
    public static function forget(string $name)
    {
        $name = static::withPrefix($name);

        static::$request->cookies->remove($name);
        Cookie::queue(Cookie::forget($name));
    }

    /**
     * Alias for forget
     *
     * @param string $name
     * @return void
     */
    public static function delete(string $name)
    {
        static::forget($name);
    }

    /**
     * Alias for forget
     *
     * @param string $name
     * @return void
     */
    public static function unset(string $name)
    {
        static::forget($name);
    }

    /**
     * Alias for forget
     *
     * @param string $name
     * @return void
     */
    public static function destroy(string $name)
    {
        static::forget($name);
    }

    /**
     * Determine if the given cookie exists
     *
     * @param string $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return static::has($key);
    }

    /**
     * Get a cookie
     *
     * @param string $key
     * @return mixed
     */
    public function offsetGet($key): mixed
    {
        return static::get($key);
    }

    /**
     * Set a cookie
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        static::set($key, $value);
    }

    /**
     * Unset a cookie
     *
     * @param string $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        static::forget($key);
    }

    public function __isset($name)
    {
        return static::has($name);
    }

    public function __get($name)
    {
        return static::get($name);
    }

    public function __set($name, $value)
    {
        static::set($name, $value);
    }

    public function __unset($name)
    {
        static::forget($name);
    }

    public function __call($method, $parameters)
    {
        return Cookie::{$method}(...$parameters);
    }
}
