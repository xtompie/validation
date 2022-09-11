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
    ->group()
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

### Nested Target

Targets can be nested e.g.

```php
    $validation = Validation::of(['person' => ['name' => 'John']])
        ->key('person')
        ->nested()->key('name')->required()->lengthMin(10)
    ;
    $validation->errors()->first()->space(); // person.name
```

After nested() function targets are related to last target.
Nested can be reset by `group()` or `main()` target.
Space in error is automaticly generated.


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

namespace App\Shared\Validation;

use App\Shared\Dao\Dao;
use Xtompie\Validation\Validation as BaseValidation;

class Validation extends BaseValidation
{
    public function __construct(
        protected Dao $dao,
    ) {}

    protected function msgs(): array
    {
        return array_merge(parent::msgs(), [
            'dao_not_exists' => 'Value {value} already exists',
        ]);
    }

    public function trim(): static
    {
        return $this->filter(fn($v) => trim($v));
    }

    public function digit($msg = 'Only digits allowed', $key = 'digit'): static
    {
        return $this->validator(fn($v) => ctype_digit($v) ? Result::ofSucces() : Result::ofErrorMsg($msg, $key));
    }

    public function daoNotExists(string $table, string $field, ?string $exceptId = null, ?string $msg = null)
    {
        return $this->validator(fn ($v) => $this->test(
            !$this->dao->exists($table, [$field => $v, 'id !=' => $exceptId]),
            'dao_not_exists',
            $msg,
            ['{value}' => $v]
        ));
    }
}

namespace App\User\Application\Service;
use App\Shared\Validation\Validation;

class CreateUserService
{
    public function __construct(
        protected Validation $validation,
    ) {}

    public function __invoke(string $email): Result
    {
        $result = $this->validation->subject($email)
            ->required()
            ->email()
            ->daoNotExists('user', 'email')
        ;
        if ($result->fail()) {
            return $result;
        }

        // create user

        return Result::ofSuccess();
    }
}

```
