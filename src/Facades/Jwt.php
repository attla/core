<?php

namespace Attla\Facades;

/**
 * @method static string encode(array|\Stdclass $headerOrPayload, array|\Stdclass $payload = null)
 * @method static mixed decode(mixed $jwt, bool $assoc = false)
 * @method static string sign(array|\Stdclass $data, int $ttl = 30, array $header = [])
 * @method static string id($id)
 * @method static string sid($id, $secret = null)
 *
 * @see \Attla\Jwt
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
