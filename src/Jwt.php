<?php

namespace Attla;

class Jwt
{
    /**
     * Modified implementation of a JWT
     *
     * @param array|\Stdclass $headerOrPayload
     * @param array|\Stdclass|null $payload
     * @return string
     */
    public static function encode($headerOrPayload, $payload = null)
    {
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
        $header = Encrypter::encode($header);

        return $header . '_' . $payload . '_' . Encrypter::md5($header . $payload);
    }

    /**
     * Check if JWT is valid and returns payload
     *
     * @param string $jwt
     * @param bool $assoc
     * @return mixed
     */
    public static function decode($jwt, bool $assoc = false)
    {
        if (!$jwt || !is_string($jwt)) {
            return false;
        }

        $jwt = explode('_', $jwt);

        if (count($jwt) != 3 || $jwt[2] != Encrypter::md5($jwt[0] . $jwt[1])) {
            return false;
        }

        if (
            $header = Encrypter::decode($jwt[0])
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
     * @return string
     */
    public static function sign(array|object $data, int $ttl = 1800)
    {
        return self::encode([
            'ttl' => time() > $ttl ? time() + ($ttl * 60) : $ttl,
            'iss' => $_SERVER['HTTP_HOST'],
            'bwr' => browser() . substr(browser_version(), 0, 2),
            'ip' => ip(),
        ], $data);
    }

    /**
     * Generate a unique identifier of anything
     *
     * @param mixed $id
     * @return string
     */
    public static function id($id)
    {
        if (is_null($id)) {
            $id = '';
        }

        return self::encode($id);
    }

    /**
     * Generate a unique identifier by secret
     *
     * @param mixed $id
     * @return string
     */
    public static function sid($id, $secret = null)
    {
        if (!$secret) {
            $secret = Encrypter::md5(config('encrypt.secret'));
        }

        if (is_null($id)) {
            $id = '';
        }

        return self::encode(['k' => $secret], $id);
    }
}
