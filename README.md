# Validation

Validation component to validate models, input data.
Handle any data types - arrays, objects, scalars, getter methods.
Easy to extend.
Type hinting / autocompletion.
Fluent syntax.

```php
use Xtompie\Validation\Validation;

$result = Validation::of($input)
    ->key('email')->required()->email()
    ->key('password')->required()->min(3)
    ->main('password')->callback(fn($input) => $input['email'] != $input['password'])
    ->group()
    ->key('email')->callback(fn($email) => !inUse($email))
    ->result();
```

`$result` is [`Xtompie\Result\Result`](https://github.com/xtompie/result)

## Requiments

PHP >= 8.0

## Installation

Using [composer](https://getcomposer.org/)

```
composer require xtompie/validation
```

## Docs

### Subject

Validation subject can be provided by

```php
Validation::of($input);
Validation::new()->subject($input);
Validation::new()->validate($input);
```

### Groups

```php
Validation::of($input)
    /* Group 1 */
    ->group()
    /* Group 2 */
    ->group()
    /* Group 3 */
    ;
```

If an error occurs during group validation, subsequent groups will not be validated and validation will stop.

### Targets

```php
Validation::new()
    ->main() // validation will target main subject
    ->property($name) // when subject is an object, target property named $name
    ->method($name) // when subject is an object, target getter method named $name
    ->key($key) // when subject is an array, target array value where key is $key
    ->take($callback) // custom target $callback, as first argument main subject will be given
;
```

### Filters

Filters are applied before validators

```php
Validation::new()
    ->key('name')
        ->filter(fn($x) => ucfirst($x)) // custom callback filter
        ->trim()
;
```

### Required/Optional

Targets are optional by default. If target is required use required method.

```php
Validation::new()
    ->key('name')->required()
;
```

### Validators

```php
Validation::new()
    ->key('name')
    // raw validator, validator return Result
    ->validator(fn ($value) => strlen($value) !== 13 ? Result::ofSuccess() : Result::ofErrorMsg('Length can not be 13'))
    // custom callback
    ->callback(fn ($value) => strlen($value) !== 13, 'Length can not be 13')
    ->notBlank('Fill name!')
;
```

All list validator in source

### Scalars

```php
$ok = Validation::of($email)->required()->email()->success();
```

If no target is provided, then the main target, validation subject, will be used.

### Validation feedback

```php
$v = Validation::new();
$v->result(); // Xtompie\Result\Result
$v->errors(); // Xtompie\Result\ErrorCollection
$v->error(); // ?Xtompie\Result\Error first error
$v->success(); // bool
$v->fail(); // bool
```
### Extending

Component consists of 3 elements.

1. ValidationValidator - builder and validator.
2. ValidationCore - wrapper for the ValidationValidator. Gives fluent syntax, deals with validation subject.
3. Validation - extends ValidationCore by inheritance. Gives concrete validations, filters, messages, keys.

#### Inheritance

Validation or ValidationCore can be extended by inheritance.

```php

namespace App\Core\Validation;

use Xtompie\Validation\Validation as BaseValidation;

class Validation extends BaseValidation
{
    public function trim(): static
    {
        return $this->filter(fn($v) => trim($v));
    }

    public function digit($msg = 'Only digits allowed', $key = 'digit'): static
    {
        return $this->validator(fn($v) => ctype_digit($v) ? Result::ofSucces() : Result::ofErrorMsg($msg, $key));
    }
}
```

#### Facade

```php

namespace App\Import\Foobar;

use App\Core\Validation\Validation as BaseValidation;

class Validation extends BaseValidation
{
    public function typePhone(): static
    {
        return $this->required()->trim()->digit();
    }
}
```

#### Model

```php

namespace App\Import\Foobar;

use App\Core\Validation\Validation as BaseValidation;

class ModelValidation extends BaseValidation
{
    public function phone(): static
    {
        return $this->property('phone')->required()->trim()->digit();
    }
}
```

#### Default messages customize

```php

namespace App\Core\Validation;

use Xtompie\Validation\Validation as BaseValidation;

class Validation extends BaseValidation
{
    public function notBlank(string $msg = 'Field required', string $key = 'not_blank'): static
    {
        return parent::notBlank($msg, $key);
    }
}
```

#### Switch context


```php
use Xtompie\Validation\Validation;

class CoreValidation extends Validation
{
    public function notBlank(string $msg = 'Field required', string $key = 'not_blank'): static
    {
        return parent::notBlank($msg, $key);
    }
}

class CLIValidation extends Validation
{
    public function myCustomValidator(): static
    {
        return $this->validator(fn($v) => /* ...*/);
    }
}

$validation = CoreValidation::new();
$validation->property('a')->notBlank();

// swtich to CLIValidation
$validation = CLIValidation::ofValidator($validation->validationValidator());
$validation->property('b')->myCustomValidator();

// switch back to CoreValidation
$validation = CoreValidation::ofValidator($validation->validationValidator());

```