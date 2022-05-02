<?php

namespace Attla;

class Jwt
{
    /**
     * Modified implementation of a JWT
     *
     * @param array|\Stdclass $headerOrPayload
     * @param array|\Stdclass|null $payload
     * @param string $secret
     * @return string
     */
    public static function encode(
        $headerOrPayload,
        $payload = null,
        string $secret = ''
    ): string {
        if (is_null($payload)) {
            $payload = $headerOrPayload;
            $header = [];
        } else {
            $header = (array) $headerOrPayload;
        }

        if (!isset($header['k'])) {
            $header['k'] = Encrypter::generateKey(6);
        }

        $payload = Encrypter::encode($payload, $header['k']);
        $header = Encrypter::encode($header, $secret);

        return $header . '_' . $payload . '_' . Encrypter::md5($header . $payload, $secret);
    }

    /**
     * Check if JWT is valid and returns payload
     *
     * @param string $jwt
     * @param bool $assoc
     * @param string $secret
     * @return mixed
     */
    public static function decode($jwt, bool $assoc = false, string $secret = '')
    {
        if (!$jwt || !is_string($jwt)) {
            return false;
        }

        $jwt = explode('_', $jwt);

        if (count($jwt) != 3 || $jwt[2] != Encrypter::md5($jwt[0] . $jwt[1], $secret)) {
            return false;
        }

        if (
            $header = Encrypter::decode($jwt[0], $secret)
            and is_object($header)
            and $payload = Encrypter::decode($jwt[1], $header->k, $assoc)
            and $payload
        ) {
            if (isset($header->ttl) && time() > $header->ttl) {
                return false;
            }

            if (isset($header->iss) && $_SERVER['HTTP_HOST'] != $header->iss) {
                return false;
            }

            if (isset($header->bwr) && browser() . substr(browser_version(), 0, 2) != $header->bwr) {
                return false;
            }

            if (isset($header->ip) && ip() != $header->ip) {
                return false;
            }

            return $payload;
        }

        return false;
    }

    /**
     * Create a JWT with security validations
     *
     * @param array|object|\Stdclass $data
     * @param int $ttl
     * @param array $header
     * @param string $secret
     * @return string
     */
    public static function sign(
        array|object $data,
        int $ttl = 30,
        array $header = [],
        string $secret = ''
    ): string {
        return self::encode(
            array_merge([
                'ttl' => time() > $ttl ? time() + ($ttl * 60) : $ttl,
                'iss' => $_SERVER['HTTP_HOST'],
                'bwr' => browser() . substr(browser_version(), 0, 2),
                'ip' => ip(),
            ], $header),
            $data,
            $secret
        );
    }

    /**
     * Generate a unique identifier of anything
     *
     * @param mixed $id
     * @param string $secret
     * @return string
     */
    public static function id($id, string $secret = ''): string
    {
        if (is_null($id)) {
            $id = '';
        }

        return self::encode($id, null, $secret);
    }

    /**
     * Generate a unique identifier by secret
     *
     * @param mixed $id
     * @param string $secret
     * @return string
     */
    public static function sid($id, string $secret = ''): string
    {
        if (is_null($id)) {
            $id = '';
        }

        return self::encode([
            'k' => Encrypter::md5($secret),
        ], $id, $secret);
    }
}
