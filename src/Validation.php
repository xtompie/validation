<?php

declare(strict_types=1);

namespace Xtompie\Validation;

use Xtompie\Result\Error;

class Validation extends ValidationCore
{
    public function required(): static
    {
        $this->validator->required(true);
        return $this->notBlank('This value must not be blank', 'required');
    }

    public function notBlank(string $msg = 'This value must not be blank', string $key = 'not_blank'): static
    {
        return $this->validator(fn(mixed $value) =>
            false === $value || (empty($value) && '0' != $value) ? Error::of($msg, $key) : null
        );
    }

    public function min(int $min, string $msg = 'This value should be greather than or equal {min}', string $key = 'min'): static
    {
        return $this->validator(function(mixed $value) use ($min, $msg, $key) {
            if ($value < $min) {
                return Error::of(str_replace("{min}", (string)$min, $msg), $key);
            }
        });
    }

    public function digit($msg = 'Only digits allowed', $key = 'digit'): static
    {
        return $this->validator(fn($v) => ctype_digit($v) ? null : Error::of($msg, $key));
    }

    public function callback(callable $callback, string $msg = '', string $key = ''): static
    {
        return $this->validator(fn(mixed $value) => $callback($value) ? null : Error::of($msg, $key));
    }
}
