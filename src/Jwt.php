<?php

namespace Attla;

/**
 * @method static string encode()
 * @method static mixed decode(string $jwt, bool $assoc = false)
 * @method static mixed fromString(string $jwt, bool $assoc = false)
 * @method static mixed parseString(string $jwt, bool $assoc = false)
 * @method static mixed parse(string $jwt, bool $assoc = false)
 * @method static self payload($value)
 * @method static self secret(string $secret)
 * @method static self same(string $entropy)
 * @method static self exp(int|\Carbon\CarbonInterface $exp = 30)
 * @method static self iss(string $value = '')
 * @method static self bwr()
 * @method static self ip()
 * @method static self sign(int|\Carbon\CarbonInterface $exp = 30)
 * @method static string id($value)
 * @method static string sid($value)
 *
 * @see \Attla\JwtFactory
 */
class Jwt
{
    /**
     * JWT factory instance
     *
     * @var JwtFactory
     */
    protected JwtFactory $factory;

    public function __construct()
    {
        $this->factory = new JwtFactory();
    }

    public function __toString(): string
    {
        return $this->factory->encode();
    }

    public function __call($name, $arguments)
    {
        if (!method_exists($this->factory, $name)) {
            throw new \BadMethodCallException(
                sprintf("The method '%s' doesn't exists in Jwt Class", $name)
            );
        }

        $result = $this->factory->{$name}(...$arguments);
        return $result instanceof JwtFactory ? $this : $result;
    }

    public static function __callStatic($name, $arguments)
    {
        return (new static())->{$name}(...$arguments);
    }
}
