<?php

namespace MichaelRavedoni\LaravelValueObjects\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use MichaelRavedoni\LaravelValueObjects\Contracts\ValueObject;

/**
 * ValueObjectCast
 *
 * A generic cast for value objects implementing the ValueObject contract.
 * Can be used to cast attributes to instances of your custom value objects.
 */
class ValueObjectCast implements CastsAttributes
{
    /**
     * @var string The fully qualified class name of the value object to cast to.
     */
    protected string $valueObjectClass;

    /**
     * Create a new ValueObjectCast instance.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $valueObjectClass)
    {
        if (! is_subclass_of($valueObjectClass, ValueObject::class)) {
            throw new InvalidArgumentException(
                'The provided class must implement ' . ValueObject::class . '.'
            );
        }
        $this->valueObjectClass = $valueObjectClass;
    }

    /**
     * Cast the given value from the database.
     *
     * @return \MichaelRavedoni\LaravelValueObjects\Contracts\ValueObject|null
     */
    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        // Use the static 'make' method of the value object for creation
        return ($this->valueObjectClass)::make($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @return mixed
     */
    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        // If it's already an instance of the expected value object, return its raw value
        if ($value instanceof $this->valueObjectClass) {
            return $value->value();
        }

        // Otherwise, try to create the value object to extract its raw value.
        // This allows assigning a raw string/int directly (e.g., $athlete->gender = 'w')
        try {
            return ($this->valueObjectClass)::make($value)->value();
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("Invalid value for {$key}: {$value}. " . $e->getMessage());
        }
    }
}
