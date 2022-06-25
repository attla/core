<?php

namespace Attla;

use Illuminate\Support\Str;
use Illuminate\Support\Enumerable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

trait AbstractData
{
    /**
     * Store property values
     *
     * @return array
     */
    protected $dtoData = [];

    /**
     * Get all properties from object
     *
     * @var object $object
     * @return array
     */
    private function getProperties(object $object)
    {
        $properties = [];
        $reflection = new \ReflectionObject($object);

        do {
            $properties = array_merge($properties, $reflection->getProperties());
        } while ($reflection = $reflection->getParentClass());

        foreach ($properties as $key => $property) {
            $property->setAccessible(true);
            $properties[$property->getName()] = $property->getValue($object);
            unset($properties[$key]);
        }

        return $properties;
    }

    /**
     * get data from source
     *
     * @param mixed $value
     * @return object
     */
    private function getDataFromSource($value)
    {
        if (is_object($value)) {
            return $value;
        } elseif ($value instanceof Enumerable) {
            $value = $value->all();
        } elseif ($value instanceof Arrayable) {
            $value = $value->toArray();
        } elseif ($value instanceof Jsonable) {
            $value = json_decode($value->toJson(), true);
        } elseif ($value instanceof \JsonSerializable) {
            $value = (array) $value->jsonSerialize();
        } elseif ($value instanceof \Traversable) {
            $value = iterator_to_array($value);
        }

        return (object) $value;
    }

    /**
     * Set properties to destination
     *
     * @var object $destination
     * @var array $properties
     * @return void
     */
    private function setProperties(
        object $destination,
        array $properties = []
    ) {
        foreach ($properties as $name => $value) {
            if (
                !is_numeric($name)
                && $name != 'dtoData'
            ) {
                $destination->set($name, $value);
            }
        }
    }

    /**
     * Map properties from source to destination
     *
     * @var object $source
     * @var object $destination
     * @return void
     */
    private function mapProperties(
        object $source,
        object $destination
    ) {
        $this->setProperties(
            $destination,
            array_merge(
                $this->getProperties($destination),
                $this->getProperties($this->getDataFromSource($source))
            )
        );
    }

    /**
     * Initialize the data transfer object
     *
     * @var object|array $data
     * @return void
     */
    protected function init(object|array $source = [])
    {
        $this->mapProperties(
            is_array($source) ? (object) $source : $source,
            $this
        );
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
     * Create new instance from other source
     *
     * @param object|array $source
     * @return static
     */
    public static function from(object|array $source): static
    {
        return new static($source);
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
     * @var object|array $source
     * @return void
     */
    public function __construct(object|array $source = [])
    {
        $this->init($source);
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
