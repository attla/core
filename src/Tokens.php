<?php

namespace Attla;

use Illuminate\Support\Str;

class Tokens
{
    /**
     * Token prefix name
     *
     * @var string
     */
    protected $prefix = '__';

    /**
     * Tokens of the application
     *
     * @var array
     */
    protected $tokens = [];

    /**
     * Add the prefix if there is none
     *
     * @param string $key
     * @return string
     */
    public function withPrefix(string $key)
    {
        if (!Str::startsWith($key, $this->prefix)) {
            $key = $this->prefix . $key;
        }

        return $this->normalizeKey($key);
    }

    /**
     * Normalize a key
     *
     * @param string $key
     * @return string
     */
    public function normalizeKey(string $key)
    {
        return strtr($key, '. ', '__');
    }

    /**
     * Set a token
     *
     * @param string $key
     * @param mixed $value
     * @param integer $ttl
     * @param bool $encoded
     * @return void
     */
    public function set(string $key, $value, int $ttl = 1800, bool $encoded = false)
    {
        $this->tokens[$this->withPrefix($key)] = [
            'value' => $value,
            'ttl' => time() > $ttl ? time() + $ttl : $ttl,
            'encoded' => $encoded,
        ];

        return $this->getLastValue();
    }

    /**
     * Alias for set
     *
     * @param string $key
     * @param mixed $value
     * @param integer $ttl
     * @param bool $encoded
     * @return void
     */
    public function store(string $key, $value, int $ttl = 1800, bool $encoded = false)
    {
        return $this->set($key, $value, $ttl, $encoded);
    }

    /**
     * Set a token without the prefix
     *
     * @param string $key
     * @param mixed $value
     * @param integer $ttl
     * @return void
     */
    public function setCustom(string $key, $value, int $ttl = 1800)
    {
        $this->tokens[$this->normalizeKey($key)] = [
            'value' => $value,
            'ttl' => time() > $ttl ? time() + $ttl : $ttl,
            'encoded' => false,
        ];

        return $this->getLastValue();
    }

    /**
     * Alias for setCustom
     *
     * @param string $key
     * @param mixed $value
     * @param integer $ttl
     * @return void
     */
    public function storeCustom(string $key, $value, int $ttl = 1800)
    {
        return $this->setCustom($key, $value, $ttl);
    }

    /**
     * Set encoded token
     *
     * @param string $key
     * @param mixed $value
     * @param integer $ttl
     * @return void
     */
    public function setEncoded(string $key, $value, int $ttl = 1800)
    {
        $value = Encrypter::encode($value);
        return $this->set($key, $value, $ttl, true);
    }

    /**
     * Alias for setEncoded
     *
     * @param string $key
     * @param mixed $value
     * @param integer $ttl
     * @return void
     */
    public function storeEncoded(string $key, $value, int $ttl = 1800)
    {
        return $this->set($key, $value, $ttl);
    }

    /**
     * Set jwt token
     *
     * @param string $key
     * @param mixed $value
     * @param integer $ttl
     * @return void
     */
    public function setSign(string $key, $value, int $ttl = 1800)
    {
        $value = \Jwt::sign($value, $ttl);
        return $this->set($key, $value, $ttl);
    }

    /**
     * Alias for setSign
     *
     * @param string $key
     * @param mixed $value
     * @param integer $ttl
     * @return void
     */
    public function storeJwt(string $key, $value, int $ttl = 1800)
    {
        return $this->setSign($key, $value, $ttl);
    }

    /**
     * Delete a token by name
     *
     * @param string $key
     * @return void
     */
    public function delete(string $key)
    {
        $this->set($key, '', -1);
    }

    /**
     * Alias for delete
     *
     * @param string $key
     * @return void
     */
    public function unset(string $key)
    {
        $this->delete($key);
    }

    /**
     * Alias for delete
     *
     * @param string $key
     * @return void
     */
    public function destroy(string $key)
    {
        $this->delete($key);
    }

    /**
     * Get a token
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        $key = $this->withPrefix($key);

        if (isset($this->tokens[$key])) {
            return $this->value($this->tokens[$key]);
        }

        return null;
    }

    /**
     * Get a token encoded
     *
     * @param string $key
     * @return mixed
     */
    public function getEncoded(string $key)
    {
        $key = $this->withPrefix($key);

        if (isset($this->tokens[$key])) {
            return Encrypter::decode($this->tokens[$key]);
        }

        return null;
    }

    /**
     * Resolve a token value
     *
     * @param mixed $value
     * @return mixed
     */
    public function value($value)
    {
        $encoded = false;

        if (is_array($value)) {
            $encoded = $value['encoded'];
            $value = $value['value'];
        }

        if ($encoded) {
            return Encrypter::decode($value);
        }

        if ($jwtDecoded = \Jwt::decode($value)) {
            return $jwtDecoded;
        }

        return $value;
    }

    /**
     * Get all tokens
     *
     * @return array
     */
    public function getAll()
    {
        return $this->tokens;
    }

    /**
     * Get all tokens to store on cookies
     *
     * @return array
     */
    public function getAllToStore()
    {
        return array_filter($this->tokens, function ($item) {
            if (is_array($item) && isset($item['value'], $item['ttl'])) {
                return $item;
            }
        });
    }

    /**
     * Get last token value
     *
     * @return array
     */
    public function getLastValue()
    {
        return $this->value(end($this->tokens));
    }

    /**
     * Set token prefix
     *
     * @param string $prefix
     * @return void
     */
    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Get the prefix
     *
     * @return string
     */
    public function prefix()
    {
        return $this->prefix;
    }

    /**
     * Check if a token exists
     *
     * @param string $key
     * @return void
     */
    public function exists($key)
    {
        return isset($this->tokens[$this->withPrefix($key)]);
    }

    public function __isset($key)
    {
        return $this->exists($key);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $value)
    {
        $this->tokens[$this->withPrefix($key)] = $value;
    }
}
