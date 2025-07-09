<?php

namespace MichaelRavedoni\LaravelValueObjects\Contracts;

use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Stringable;

interface ValueObject extends Jsonable, JsonSerializable, Stringable
{
    /**
     * Get the raw value of the value object.
     */
    public function value(): mixed;

    /**
     * Create a new instance from a raw value.
     */
    public static function make(mixed $value): static;
}
