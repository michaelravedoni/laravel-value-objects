<h1 align="center">Laravel Value Objects</h1> <br>

<p align="center"></p>

<div align="center">
  <strong>Simply create value objects in Laravel.</strong>
</div>
<div align="center">
 
</div>

<div align="center">
  <h3>
    <a href="https://github.com/michaelravedoni/laravel-value-objects#documentation">Documentation</a>
    <span> | </span>
    <a href="#contributing">
      Contributing
    </a>
  </h3>
</div>

<div align="center">
  <sub>Built with ❤︎ by
  <a href="https://michael.ravedoni.com/en">Michael Ravedoni</a> and
  <a href="https://github.com/michaelravedoni/prathletics/contributors">
    contributors
  </a>
</div>

## Introduction

[![Latest Version](https://img.shields.io/github/release/michaelravedoni/laravel-value-objects.svg?style=flat-square)](https://github.com/michaelravedoni/laravel-value-objects/releases)
![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/michaelravedoni/laravel-value-objects/run-tests.yml?branch=main&label=tests)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/michaelravedoni/laravel-value-objects.svg?style=flat-square)](https://packagist.org/packages/michaelravedoni/laravel-value-objects)

A simple, lightweight Laravel package for creating value objects, including an Artisan command for generating them and a generic Eloquent *casting* system.

## ✨ Features

  * **Basic Value Object**: An abstract class for structuring your value objects.
  * **Contractual interface**: For strong typing and consistent implementation.
  * **Generic Eloquent Casting**: Easily converts model attributes into instances of your custom value objects and vice versa.
  * **Artisan command**: Quickly generates value object skeletons.
  * **Built-in validation**: Add your validation logic directly into the constructor of your value objects.
  * **Extensive compatibility**: Supports Laravel 9, 10, 11, 12 and PHP 8.1, 8.2, 8.3, 8.4.

## 🚀 Installation

This package can be installed through Composer :

```bash
composer require michaelravedoni/laravel-value-objects
```

The package will be automatically discovered by Laravel.

## 💡 Documentation

### Creating a Value Object

The easiest way to create a new value object is to use the Artisan command provided by the :

```bash
php artisan make:value-object MyCustomValue
```

This will create an `app/ValueObjects/MyCustomValue.php` file with a code skeleton.

You then need to implement the constructor to define the value and add your validation logic, as well as the `value()` method to return the raw value.

**Example: An `Email` value object**.

```php
// app/ValueObjects/Email.php
<?php

namespace App\ValueObjects;

use MichaelRavedoni\LaravelValueObjects\ValueObjects\BaseValueObject;
use InvalidArgumentException;

class Email extends BaseValueObject
{
    protected string $value;

    public function __construct(string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format: {$value}");
        }
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function getDomain(): string
    {
        return substr($this->value, strpos($this->value, '@') + 1);
    }
}
```

### Casting with Eloquent

To use your value object with an Eloquent model, you can cast it directly using the package's generic `ValueObjectCast` in your `$casts` array.

```php
// app/Models/User.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MichaelRavedoni\LaravelValueObjects\Casts\ValueObjectCast;
use App\ValueObjects\Email; // Importez votre objet valeur

class User extends Model
{
    protected $fillable = [
        'name',
        'email',
    ];

    protected $casts = [
        'email' => ValueObjectCast::class . ':' . Email::class,
    ];
}
```

### Accessing Values and Methods

Once cast, you can access your attribute as an instance of your value object, by calling its methods or by treating it as a string (using the `__toString()` method inherited from `BaseValueObject`).

```php
use App\Models\User;
use App\ValueObjects\Email;

// Create a user
$user = User::create([
    'name' => 'Alice',
    'email' => new Email('alice@example.com'), // Accepts a value object instance
]);

// Or directly a raw value (the cast will convert it)
$user2 = User::create([
    'name' => 'Bob',
    'email' => 'bob@test.com',
]);

// Access the value object
$user = User::find(1);

echo "Full email address : " . $user->email; // alice@example.com (via __toString())
echo "Email domain : " . $user->email->getDomain(); // example.com

// Update the
$user->email = 'new.email@domain.com'; // You can assign a string
$user->save();

$user->email = new Email('another@mail.org'); // Or a new instance of a value object
$user->save();

// Manage null values (if the column is nullable)
$userWithNullEmail = User::create(['name' => 'John', 'email' => null]);
if ($userWithNullEmail->email === null) {
    echo "The email is null.";
}
```

## 🎬 Usage

Let's take the example of `Gender' to illustrate a concrete case.

**1. Create the `Gender` Value Object :**.

```bash
php artisan make:value-object Gender
```

Edit the file `app/ValueObjects/Gender.php` :

```php
// app/ValueObjects/Gender.php
<?php

namespace App\ValueObjects;

use MichaelRavedoni\LaravelValueObjects\ValueObjects\BaseValueObject;
use InvalidArgumentException;

class Gender extends BaseValueObject
{
    protected string $value; // 'm' ou 'w'

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
            'm' => 'Man',
            'w' => 'Woman',
            default => 'Not specified',
        };
    }

    public function getDescription(): string
    {
        return match ($this->value) {
            'm' => 'Type of athlete : Male',
            'w' => 'Type of athlete : Female',
            default => 'Gender not specified',
        };
    }
}
```

**2. Use in the `Athlete` Model :**

Make sure that your `Athlete` model has a `gender` column (type `char(1)` or `string`).

```php
// app/Models/Athlete.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MichaelRavedoni\LaravelValueObjects\Casts\ValueObjectCast;
use App\ValueObjects\Gender;

class Athlete extends Model
{
    protected $fillable = [
        'name',
        'gender',
    ];

    protected $casts = [
        'gender' => ValueObjectCast::class . ':' . Gender::class,
    ];
}
```

**3. Practical use :**

```php
use App\Models\Athlete;
use App\ValueObjects\Gender;

$athlete = Athlete::create([
    'name' => 'Alice Tremblay',
    'gender' => 'w',
]);

echo $athlete->gender; // Display : w
echo $athlete->gender->getLabel(); // Display : Woman
echo $athlete->gender->getDescription(); // Display : Gender of athlete : Woman

$athlete->gender = new Gender('m');
$athlete->save();

$updatedAthlete = Athlete::find($athlete->id);
echo $updatedAthlete->gender->getLabel(); // Display : Man
```

## 🤝 Contribute

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## 📜 Credits

  * **Michael Ravedoni** - *Initial work* - [michaelravedoni](https://github.com/michaelravedoni)
  * Inspired form [Michael Rubel](https://github.com/michael-rubel/laravel-value-objects) package.

See also the list of [contributors](https://github.com/michaelravedoni/laravel-value-objects/contributors) who participated in this project.

## 📄 Licence

[MIT License](https://opensource.org/licenses/MIT). Please see [License File](LICENSE.md) for more information.