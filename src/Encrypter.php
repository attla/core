<?php

namespace Attla;

class Encrypter
{
    /**
     * Encrypt a string following a salt, salt should not be passed if it is not a hash
     *
     * @param string $password
     * @param string $salt
     * @return string
     */
    public static function hash(string $password, string $salt = ''): string
    {
        if ($salt) {
            $saltLength = strlen($salt);
            if ($saltLength > 40) {
                $saltLength -= 40;
                $salt = $saltLength % 2 ? substr($salt, 0, $saltLength) : substr($salt, -$saltLength);
            }
        } else {
            $length = 47 % strlen($password);
            if ($length == 0) {
                $length = 47 % mt_rand(2, 46);
            }

            do {
                $salt = substr(self::generateKey(24), 0, $length);
            } while (!$salt);

            $saltLength = strlen($salt);
        }

        $restDiv = $saltLength % 2;
        return ($restDiv ? $salt : '') . sha1($password . $salt) . ($restDiv ? '' : $salt);
    }

    /**
     * Create a new encryption key
     *
     * @param int $length Optionally, a length of bytes to use
     * @return string
     */
    public static function generateKey(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Compare an unencrypted password with an encrypted password
     *
     * @param string $unencrypted
     * @param string $encrypted
     * @return bool
     */
    public static function hashEquals(string $unencrypted, string $encrypted)
    {
        return hash_equals($encrypted, self::hash($unencrypted, $encrypted));
    }

    /**
     * Convert array and objects to strings
     *
     * @param array|object $item
     * @return string
     */
    public static function toText($item): string
    {
        $mode = config('encrypt.mode');
        if (!in_array($mode, ['query', 'json', 'serialize'])) {
            $mode = 'query';
        }

        return $mode == 'query' ? http_build_query($item) : ($mode == 'json' ? json_encode($item) : serialize($item));
    }

    /**
     * Get secret key
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function getSecret(): string
    {
        if ($secret = config('encrypt.secret')) {
            return (string) $secret;
        }

        throw new \Exception('Encrypt secret key config not found.');
    }

    /**
     * Cipher a string
     *
     * @param string $str
     * @param string $secret
     * @return string
     */
    protected static function cipher($str, $secret)
    {
        $secret = $secret ?: self::getSecret();

        if (!$str || !$secret) {
            return '';
        }

        if (!is_string($str)) {
            $str = (string) $str;
        }

        $result = '';

        $dataLength = strlen($str) - 1;
        $secretLenght = strlen($secret) - 1;

        do {
            $result .= $str[$dataLength] ^ $secret[$dataLength % $secretLenght];
        } while ($dataLength--);

        return strrev($result);
    }

    /**
     * Encrypt a anyting with secret key
     *
     * @param mixed $data
     * @param string $secret
     * @return string
     */
    public static function encode($data, string $secret = ''): string
    {
        if (is_array($data) || is_object($data)) {
            $data = self::toText($data);
        }

        return self::urlsafeB64Encode(self::cipher($data, $secret));
    }

    /**
     * Decrypt a anyting with secret key
     *
     * @param mixed $data
     * @param string $secret
     * @param bool $assoc
     * @return mixed
     */
    public static function decode($data, string $secret = '', bool $assoc = false)
    {
        if ($result = self::cipher(self::urlsafeB64Decode($data), $secret)) {
            if (is_json($result)) {
                $result = json_decode($result, $assoc);
            } elseif (is_serialized($result)) {
                $result = unserialize($result);
                if (!$assoc) {
                    $result = (object) $result;
                }
            } elseif (is_http_query($result)) {
                parse_str($result, $array);
                $result = !$assoc ? (object) $array : $array;
            }
        }

        return $result;
    }

    /**
     * Encode a string with URL-safe Base64
     *
     * @param string $input The string you want encoded
     * @return string The base64 encode of what you passed in
     */
    public static function urlsafeB64Encode(string $data): string
    {
        return str_replace('=', '', strtr(base64_encode($data), '+/', '-.'));
    }

    /**
     * Decode a string with URL-safe Base64
     *
     * @param string $data A Base64 encoded string
     * @return string A decoded string
     */
    public static function urlsafeB64Decode(string $data): string
    {
        $remainder = strlen($data) % 4;

        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }

        return base64_decode(strtr($data, '-.', '+/'));
    }

    /**
     * Encrypt an md5 in bytes of a string
     *
     * @param mixed $str
     * @param string $secret
     * @return string
     */
    public static function md5($str, string $secret = ''): string
    {
        return self::encode(md5((string) $str, true), $secret);
    }
}
