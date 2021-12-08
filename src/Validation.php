<?php

declare(strict_types=1);

namespace Xtompie\Validation;

use Xtompie\Result\Result;

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
            false === $value || (empty($value) && '0' != $value) ? Result::ofErrorMsg($msg, $key) : Result::ofSuccess()
        );
    }

    public function min(int $min, string $msg = 'This value should be greather than or equal {min}', string $key = 'min'): static
    {
        return $this->validator(function(mixed $value) use ($min, $msg, $key) {
            return $value >= $min ? Result::ofSuccess() : Result::ofErrorMsg(str_replace("{min}", (string)$min, $msg), $key);
        });
    }

    public function digit($msg = 'Only digits allowed', $key = 'digit'): static
    {
        return $this->validator(fn($v) => ctype_digit($v) ? Result::ofSuccess() : Result::ofErrorMsg($msg, $key));
    }

    public function callback(callable $callback, string $msg = '', string $key = ''): static
    {
        return $this->validator(fn(mixed $value) => $callback($value) ? Result::ofSuccess()  : Result::ofErrorMsg($msg, $key));
    }
}
