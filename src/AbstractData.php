<?php

namespace Attla;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait AbstractData
{
    /**
     * Store property values
     *
     * @return array
     */
    protected $dtoData = [];

    /**
     * Initialize the data transfer object
     *
     * @var array $data
     * @return void
     */
    protected function init(array $data = [])
    {
        foreach (
            Arr::except(
                array_merge(
                    get_object_vars($this),
                    $data
                ),
                ['dtoData']
            ) as $name => $value
        ) {
            if (!is_numeric($name)) {
                $this->set($name, $value);

                if (property_exists($this, $name)) {
                    unset($this->{$name});
                }
            }
        }
    }

    /**
     * Get an attribute value
     *
     * @param string $name
     * @return mixed|null
     */
    protected function get(string $name)
    {
        $value = $this->dtoData[Str::camel($name)] ?? null;

        if (method_exists($this, $getter = 'get' . Str::studly($name))) {
            return $this->{$getter}($value);
        }

        return $value;
    }

    /**
     * Check if an attribute is set
     *
     * @param string $name
     * @return bool
     */
    protected function isset(string $name): bool
    {
        return isset($this->dtoData[Str::camel($name)])
            || method_exists($this, 'get' . Str::studly($name));
    }

    /**
     * Set an attribute value
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    protected function set(string $name, $value): void
    {
        $this->dtoData[Str::camel($name)] = method_exists($this, $setter = 'set' . Str::studly($name))
            ? $this->{$setter}($value)
            : $value;
    }

    /**
     * Unset an attribute
     *
     * @param string $name
     * @return void
     */
    protected function unset(string $name): void
    {
        if ($this->isset($name)) {
            unset($this->dtoData[$name]);
        }
    }

    /**
     * Fill this object with values given in associative array
     *
     * @param mixed[] $array
     * @return void
     */
    public function hydrate(array $array): void
    {
        foreach ($array as $key => $value) {
            if (!is_numeric($key)) {
                $this->set($key, $value);
            }
        }
    }

    /**
     * Extracts this object into associative array
     *
     * @return mixed[]
     */
    public function extract(): array
    {
        $data = [];
        foreach ($this->dtoData as $name => $value) {
            $data[$name] = $this->get($name);
        }

        return $data;
    }

    /**
     * Create new instance from other object
     *
     * @param object $instance
     * @return static
     */
    public static function from(object $instance): static
    {
        $destination = new static();
        $destinationReflection = new \ReflectionObject($destination);

        foreach ((new \ReflectionObject($instance))->getProperties() as $sourceProperty) {
            $value = $sourceProperty->getValue($instance);

            if ($destinationReflection->hasProperty($name = $sourceProperty->getName())) {
                $destinationReflection->getProperty($name)
                    ->setValue($destination, $value);
            }
        }

        return $destination;
    }

    /**
     * Create new instance from array of properties
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        $destination = new static();
        $destinationReflection = new \ReflectionObject($destination);

        foreach ($data as $name => $value) {
            if (!is_numeric($name) && $destinationReflection->hasProperty($name)) {
                $destinationReflection->getProperty($name)
                    ->setValue($destination, $value);
            }
        }

        return $destination;
    }

    /**
     * Get values
     *
     * @return mixed[]
     */
    public function values(): array
    {
        return $this->extract();
    }

    /**
     * Get all values
     *
     * @return mixed[]
     */
    public function all(): array
    {
        return $this->extract();
    }

    /**
     * Transform the data into an array
     *
     * @return mixed[]
     */
    public function toArray(): array
    {
        return $this->extract();
    }

    /**
     * Get the array that should be JSON serialized
     *
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return $this->extract();
    }

    /**
     * Convert the data to JSON
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Determine if the given offset exists
     *
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->isset($offset);
    }

    /**
     * Get the value for a given offset
     *
     * @param string $offset
     * @return mixed|null
     */
    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Set the value at the given offset
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Unset the value at the given offset
     *
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        $this->unset($offset);
    }

    /**
     * Create a new DTO instance
     *
     * @var array $data
     * @return void
     */
    public function __construct(array $data = [])
    {
        $this->init($data);
    }

    /**
     * Dynamically retrieve the value of an attribute
     *
     * @param string $key
     * @return mixed|null
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Dynamically set the value of an attribute
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Dynamically check if an attribute is set
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->isset($key);
    }

    /**
     * Dynamically unset an attribute
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        $this->unset($key);
    }
}
