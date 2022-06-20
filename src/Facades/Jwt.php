<?php

namespace Attla\Facades;

/**
 * @method static string encode()
 * @method static mixed decode(string $jwt, bool $assoc = false)
 * @method static mixed fromString(string $jwt, bool $assoc = false)
 * @method static mixed parseString(string $jwt, bool $assoc = false)
 * @method static mixed parse(string $jwt, bool $assoc = false)
 * @method static self payload($value)
 * @method static self secret(string $secret)
 * @method static self same(string $entropy)
 * @method static self exp(int|CarbonInterface $exp = 30)
 * @method static self iss(string $value = '')
 * @method static self bwr()
 * @method static self ip()
 * @method static self sign(int|CarbonInterface $exp = 30)
 * @method static string id($value)
 * @method static string sid($value)
 *
 * @see \Attla\JwtFactory
 */
class Jwt extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the registered name of the component
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Attla\Jwt::class;
    }
}
