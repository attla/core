<?php

namespace Attla;

use Carbon\CarbonInterface;
use Illuminate\Support\Enumerable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

class JwtFactory
{
    /**
     * The header of the JWT
     *
     * @var array
     */
    private array $header = [];

    /**
     * The payload of the JWT
     *
     * @var mixed
     */
    private $payload;

    /**
     * The secret passphrase of the JWT
     *
     * @var string
     */
    private string $secret = '';

    /**
     * Determine if the JWT is on same mode
     *
     * @var bool
     */
    private bool $same = false;

    /**
     * Encode a JWT
     *
     * @return string
     */
    public function encode(): string
    {
        if (!$this->payload) {
            throw new \Exception('The payload cannot be empty.');
        }

        $payload = Encrypter::encode($this->payload, $this->getEntropy());
        $header = Encrypter::encode(
            $this->same ? $this->header : array_random($this->header),
            $this->secret
        );

        return $header . '_'
            . $payload . '_'
            . Encrypter::md5($header . $payload, $this->secret);
    }

    /**
     * Decode the JWT if is valid
     *
     * @param string $jwt
     * @param bool $assoc
     * @return mixed
     */
    public function decode($jwt, bool $assoc = false): mixed
    {
        if (!$jwt || !is_string($jwt)) {
            return false;
        }

        $jwt = explode('_', $jwt);
        if (count($jwt) != 3) {
            return false;
        }

        [$header, $payload, $signature] = $jwt;

        if ($signature != Encrypter::md5($header . $payload, $this->secret)) {
            return false;
        }

        $header = Encrypter::decode($header, $this->secret);
        if (!$header instanceof \StdClass) {
            return false;
        }

        $payload = Encrypter::decode($payload, $header->e ?? '', $assoc);
        if (!$payload) {
            return false;
        }

        // exp validation
        if (isset($header->exp) && time() > $header->exp) {
            return false;
        }

        // iss validation
        if (isset($header->iss) && $_SERVER['HTTP_HOST'] != $header->iss) {
            return false;
        }

        // bwr validation
        if (isset($header->bwr) && $this->browser() != $header->bwr) {
            return false;
        }

        // ip validation
        if (isset($header->ip) && ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR']) != $header->ip) {
            return false;
        }

        $this->header = $this->toArray($header);
        $this->payload = $this->primitiveOrArray($payload);

        return $payload;
    }

    /**
     * Alias of decode
     *
     * @param string $jwt
     * @param bool $assoc
     * @return mixed
     */
    public function fromString(string $jwt, bool $assoc = false): mixed
    {
        return $this->decode($jwt, $assoc);
    }

    /**
     * Alias of decode
     *
     * @param string $jwt
     * @param bool $assoc
     * @return mixed
     */
    public function parseString(string $jwt, bool $assoc = false): mixed
    {
        return $this->decode($jwt, $assoc);
    }

    /**
     * Alias of decode
     *
     * @param string $jwt
     * @param bool $assoc
     * @return mixed
     */
    public function parse(string $jwt, bool $assoc = false): mixed
    {
        return $this->decode($jwt, $assoc);
    }

    /**
     * Set JWT payload
     *
     * @param mixed $value
     * @return mixed
     */
    public function payload($value): self
    {
        $this->payload = $this->primitiveOrArray($value);
        return $this;
    }

    /**
     * Alias of payload
     *
     * @param mixed $value
     * @return mixed
     */
    public function body($value): self
    {
        return $this->payload($value);
    }

    /**
     * Set JWT secret
     *
     * @param string $secret
     * @return self
     */
    public function secret(string $secret): self
    {
        $this->secret = $secret;
        return $this;
    }

    /**
     * Set JWT expiration time
     *
     * @param int|CarbonInterface $exp
     * @return self
     */
    public function exp(int|CarbonInterface $exp = 30): self
    {
        if ($exp instanceof CarbonInterface) {
            $exp = $exp->timestamp;
        }

        $this->header['exp'] = time() > $exp ? time() + ($exp * 60) : $exp;
        return $this;
    }

    /**
     * Set JWT iss validation
     *
     * @return self
     */
    public function iss(string $value = ''): self
    {
        $this->header['iss'] = $value ?: $_SERVER['HTTP_HOST'];
        return $this;
    }

    /**
     * Set JWT browser validation
     *
     * @return self
     */
    public function bwr(): self
    {
        $this->header['bwr'] = $this->browser();
        return $this;
    }

    /**
     * Set JWT IP validation
     *
     * @return self
     */
    public function ip(): self
    {
        $this->header['ip'] = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'];
        return $this;
    }

    /**
     * Signs a JWT with security validations
     *
     * @return self
     */
    public function sign(int|CarbonInterface $exp = 30): self
    {
        $this->exp($exp);
        $this->iss();
        $this->bwr();
        $this->ip();
        return $this;
    }

    /**
     * Generate a unique identifier
     *
     * @param mixed $value
     * @return string
     */
    public function id($value): string
    {
        return $this->payload($value)->encode();
    }

    /**
     * Always generate the same identifier
     *
     * @param mixed $value
     * @return string
     */
    public function sid($value): string
    {
        return $this->same()->id($value);
    }

    /**
     * Get entropy token
     *
     * @return string
     */
    public function getEntropy()
    {
        if ($this->same) {
            return $this->header['e'];
        }

        return $this->header['e'] = Encrypter::generateKey(6);
    }

    /**
     * Set JWT to same mode
     *
     * @param string $entropy
     * @return self
     */
    public function same(string $entropy = ''): self
    {
        $this->same = true;
        $this->header['e'] = Encrypter::md5($entropy ?: $this->secret);
        return $this;
    }

    /**
     * Get browser
     *
     * @param string $entropy
     * @return self
     */
    protected function browser()
    {
        return function_exists('browser') && function_exists('browser_version')
            ? browser() . explode('.', browser_version())[0]
            : trim($_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * Convert a value to array
     *
     * @param mixed $value
     * @return array
     */
    protected function toArray($value)
    {
        if (is_array($value)) {
            return $value;
        } elseif ($value instanceof Enumerable) {
            return $value->all();
        } elseif ($value instanceof Arrayable) {
            return $value->toArray();
        } elseif ($value instanceof Jsonable) {
            return json_decode($value->toJson(), true);
        } elseif ($value instanceof \JsonSerializable) {
            return (array) $value->jsonSerialize();
        } elseif ($value instanceof \Traversable) {
            return iterator_to_array($value);
        }

        return (array) $value;
    }

    /**
     * Get a primitive value or array
     *
     * @param mixed $value
     * @return mixed
     */
    protected function primitiveOrArray($value)
    {
        if (
            is_numeric($value)
            || is_string($value)
            || is_array($value)
        ) {
            return $value;
        }

        return $this->toArray($value);
    }
}
