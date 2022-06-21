<?php

namespace Attla\Facades;

/**
 * @method static void setRequest(\Illuminate\Http\Request $request)
 * @method static string withPrefix(string $name)
 * @method static void setPrefix(string $prefix)
 * @method static string prefix()
 * @method static bool has(string $name)
 * @method static bool exists(string $name)
 * @method static mixed get($name = null, $default = null, $original = false)
 * @method static array getMany(array $keys)
 * @method static string|null getOriginal($name = null, $default = null)
 * @method static array all()
 * @method static mixed value($value)
 * @method static \Illuminate\Cookie\CookieJar|\Symfony\Component\HttpFoundation\Cookie set($name, $value, $minutes = 30, $path = null,  $domain = null, $secure = null, $httpOnly = true, $raw = false, $sameSite = null)
 * @method static \Illuminate\Cookie\CookieJar|\Symfony\Component\HttpFoundation\Cookie store($name, $value, $minutes = 30, $path = null,  $domain = null, $secure = null, $httpOnly = true, $raw = false, $sameSite = null)
 * @method static void forget(string $name)
 * @method static void delete(string $name)
 * @method static void unset(string $name)
 * @method static void destroy(string $name)
 * @method static void unqueue(string $name)
 *
 * @see \Attla\Cookier
 */
class Cookie extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the registered name of the component
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Attla\Cookier::class;
    }
}
