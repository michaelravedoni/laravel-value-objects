<?php

namespace MichaelRavedoni\LaravelValueObjects\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use MichaelRavedoni\LaravelValueObjects\Casts\ValueObjectCast;
use MichaelRavedoni\LaravelValueObjects\Tests\TestCase;
use MichaelRavedoni\LaravelValueObjects\ValueObjects\BaseValueObject;
use InvalidArgumentException;

// Définition d'un objet valeur de test
class TestGender extends BaseValueObject
{
    protected string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, ['m', 'w'])) {
            throw new InvalidArgumentException("Invalid gender value: {$value}. Must be 'm' or 'w'.");
        }
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return match ($this->value) {
            'm' => 'Male',
            'w' => 'Female',
            default => 'Unknown',
        };
    }
}

// Modèle de test pour l'Eloquent casting
class TestUser extends Model
{
    protected $table = 'users';
    protected $guarded = []; // Allow mass assignment for testing

    protected $casts = [
        'gender' => ValueObjectCast::class . ':' . TestGender::class,
    ];
}

class ValueObjectCastTest extends TestCase
{
    /** @test */
    public function it_can_cast_a_value_object_from_the_database()
    {
        $user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'gender' => 'm',
        ]);

        $retrievedUser = TestUser::find($user->id);

        $this->assertInstanceOf(TestGender::class, $retrievedUser->gender);
        $this->assertEquals('m', $retrievedUser->gender->value());
        $this->assertEquals('Male', $retrievedUser->gender->getLabel());
        $this->assertEquals('m', (string) $retrievedUser->gender); // Test __toString()
    }

    /** @test */
    public function it_can_cast_a_value_object_to_the_database()
    {
        $user = new TestUser([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
        $user->gender = new TestGender('w');
        $user->save();

        $this->assertEquals('w', $user->getRawOriginal('gender')); // Check the raw value stored

        $retrievedUser = TestUser::find($user->id);
        $this->assertInstanceOf(TestGender::class, $retrievedUser->gender);
        $this->assertEquals('w', $retrievedUser->gender->value());
    }

    /** @test */
    public function it_can_set_a_raw_value_and_it_gets_casted()
    {
        $user = new TestUser([
            'name' => 'Raw User',
            'email' => 'raw@example.com',
        ]);
        $user->gender = 'm'; // Assign a raw string
        $user->save();

        $retrievedUser = TestUser::find($user->id);
        $this->assertInstanceOf(TestGender::class, $retrievedUser->gender);
        $this->assertEquals('m', $retrievedUser->gender->value());
    }

    /** @test */
    public function it_handles_null_values_correctly()
    {
        $user = TestUser::create([
            'name' => 'Null Gender User',
            'email' => 'null@example.com',
            'gender' => null,
        ]);

        $retrievedUser = TestUser::find($user->id);
        $this->assertNull($retrievedUser->gender);

        $retrievedUser->gender = new TestGender('w');
        $retrievedUser->save();
        $this->assertInstanceOf(TestGender::class, $retrievedUser->gender);

        $retrievedUser->gender = null;
        $retrievedUser->save();
        $this->assertNull($retrievedUser->gender);
    }

    /** @test */
    public function it_throws_exception_for_invalid_value_when_setting_attribute()
    {
        $user = new TestUser([
            'name' => 'Invalid User',
            'email' => 'invalid@example.com',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $user->gender = 'x'; // Invalid gender value
    }
}