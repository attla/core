<?php

namespace Attla;

use Illuminate\Support\Arr;

class Config extends \ArrayObject
{
    /**
     * The configuration data
     *
     * @var array
     */
    protected $data;

    /**
     * Create a new configuration repository
     *
     * @param array $data
     * @return void
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Determine if the given configuration value exists
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return Arr::has($this->data, $key);
    }

    /**
     * Get the specified configuration value
     *
     * @param array|string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (is_array($key)) {
            return $this->getMany($key);
        }

        return Arr::get($this->data, $key, $default);
    }

    /**
     * Get many configuration values
     *
     * @param array $keys
     * @return array
     */
    public function getMany($keys)
    {
        $config = [];

        foreach ($keys as $key => $default) {
            if (is_numeric($key)) {
                [$key, $default] = [$default, null];
            }

            $config[$key] = Arr::get($this->data, $key, $default);
        }

        return $config;
    }

    /**
     * Set a given configuration value
     *
     * @param array|string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            Arr::set($this->data, $key, $value);
        }
    }

    /**
     * Prepend a value into an array configuration value
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function prepend($key, $value)
    {
        $array = $this->get($key);
        array_unshift($array, $value);

        $this->set($key, $array);
    }

    /**
     * Push a value into an array configuration value
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function push($key, $value)
    {
        $array = $this->get($key);
        $array[] = $value;

        $this->set($key, $array);
    }

    /**
     * Get all of the configuration items for the application
     *
     * @return array
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Determine if the given configuration option exists
     *
     * @param string $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * Get a configuration option
     *
     * @param string $key
     * @return mixed
     */
    public function offsetGet($key): mixed
    {
        return $this->get($key);
    }

    /**
     * Set a configuration option
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Unset a configuration option
     *
     * @param string $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        $this->set($key, null);
    }
}
