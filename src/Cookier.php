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
    public static function withPrefix(string $name): string
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
    public static function setPrefix(string $prefix): void
    {
        static::$prefix = $prefix;
    }

    /**
     * Get the prefix
     *
     * @return string
     */
    public static function prefix(): string
    {
        return static::$prefix;
    }

    /**
     * Determine if a cookie exists on the request
     *
     * @param string $name
     * @return bool
     */
    public static function has(string $name): bool
    {
        return !is_null(static::get($name, null, true));
    }

    /**
     * Alias for has
     *
     * @param string $name
     * @return bool
     */
    public static function exists(string $name): bool
    {
        return static::has($name);
    }

    /**
     * Retrieve a cookie from the request
     *
     * @param array|string|null $name
     * @param mixed $default
     * @return mixed
     */
    public static function get($name = null, $default = null, $original = false)
    {
        if (is_array($name)) {
            return static::getMany($name);
        }

        if (is_null($name)) {
            return static::all();
        }

        $value = static::$request->cookie(static::withPrefix($name), $default)
            ?: static::$request->cookie($name, $default);

        return $original ? $value : static::value($value);
    }

    /**
     * Retrieve many cookies
     *
     * @param array $keys
     * @return array
     */
    public static function getMany(array $keys): array
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
     * Retrieve a plain text cookie from the request
     *
     * @param array|string|null $name
     * @param mixed $default
     * @return string|null
     */
    public static function getOriginal($name = null, $default = null)
    {
        return static::get($name, $default, true);
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
                fn($key) => ltrim($key, static::$prefix),
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
     * @return \Symfony\Component\HttpFoundation\Cookie
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

        Cookie::queue($cookie = Cookie::make(
            $name,
            $value,
            $minutes,
            $path,
            $domain,
            $secure,
            $httpOnly,
            $raw,
            $sameSite
        ));
        return $cookie;
    }

    /**
     * Alias for set
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
     * @return \Symfony\Component\HttpFoundation\Cookie
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
     * Create a cookie that lasts "forever" (five years)
     *
     * @param string $name
     * @param string $value
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null $secure
     * @param bool $httpOnly
     * @param bool $raw
     * @param string|null $sameSite
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    public static function forever(
        $name,
        $value,
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
            2628000,
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
     * @param string|null $path
     * @param string|null $domain
     * @return void
     */
    public static function forget(string $name, $path = null, $domain = null)
    {
        static::$request->cookies->remove($name);
        Cookie::queue(Cookie::forget($name, $path, $domain));

        $name = static::withPrefix($name);
        static::$request->cookies->remove($name);
        Cookie::queue(Cookie::forget($name, $path, $domain));
    }

    /**
     * Alias for forget
     *
     * @param string $name
     * @param string|null $path
     * @param string|null $domain
     * @return void
     */
    public static function delete(string $name, $path = null, $domain = null)
    {
        static::forget($name, $path, $domain);
    }

    /**
     * Alias for forget
     *
     * @param string $name
     * @param string|null $path
     * @param string|null $domain
     * @return void
     */
    public static function unset(string $name, $path = null, $domain = null)
    {
        static::forget($name, $path, $domain);
    }

    /**
     * Alias for forget
     *
     * @param string $name
     * @param string|null $path
     * @param string|null $domain
     * @return void
     */
    public static function expire(string $name, $path = null, $domain = null)
    {
        static::forget($name, $path, $domain);
    }

    /**
     * Alias for forget
     *
     * @param string $name
     * @param string|null $path
     * @param string|null $domain
     * @return void
     */
    public static function destroy(string $name, $path = null, $domain = null)
    {
        static::forget($name, $path, $domain);
    }

    /**
     * Unqueue a cookie by name
     *
     * @param string $name
     * @param string|null $path
     * @return void
     */
    public static function unqueue(string $name, $path = null)
    {
        Cookie::unqueue(static::withPrefix($name), $path);
    }

    /**
     * Determine if a cookie has been queued
     *
     * @param string $key
     * @param string|null $path
     * @return bool
     */
    public static function hasQueued(string $key, $path = null)
    {
        return Cookie::hasQueued(static::withPrefix($key), $path);
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
