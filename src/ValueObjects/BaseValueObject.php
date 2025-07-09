<?php

namespace MichaelRavedoni\LaravelValueObjects\ValueObjects;

use MichaelRavedoni\LaravelValueObjects\Contracts\ValueObject;

/**
 * BaseValueObject
 *
 * Abstract base class for value objects.
 * Provides default implementations for common methods like __toString(), jsonSerialize(), toJson().
 * Requires child classes to implement the `value()` method.
 */
abstract class BaseValueObject implements ValueObject
{
    /**
     * Get the raw value of the value object.
     */
    abstract public function value(): mixed;

    /**
     * Create a new instance from a raw value.
     * This method is crucial for the generic ValueObjectCast.
     */
    public static function make(mixed $value): static
    {
        return new static($value);
    }

    /**
     * Convert the object to its string representation.
     */
    public function __toString(): string
    {
        $value = $this->value();
        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }
        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value); // Fallback for complex types
    }

    /**
     * Specify data which should be serialized to JSON.
     */
    public function jsonSerialize(): mixed
    {
        return $this->value();
    }

    /**
     * Convert the object to JSON.
     *
     * @param  int  $options
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the object to its string representation.
     */
    public function toString(): string
    {
        return $this->__toString();
    }

    /**
     * Get the internal property value.
     */
    public function __get(string $name): mixed
    {
        return $this->{$name};
    }

    /**
     * Check if objects are instances of same class
     * and share the same properties and values.
     *
     * @param  ValueObject<int|string, mixed>  $object
     */
    public function equals(ValueObject $object): bool
    {
        return $this == $object;
    }

    /**
     * Inversion for `equals` method.
     *
     * @param  ValueObject<int|string, mixed>  $object
     */
    public function notEquals(ValueObject $object): bool
    {
        return ! $this->equals($object);
    }
}
