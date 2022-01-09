<?php

declare(strict_types=1);

namespace Xtompie\Validation;

use Xtompie\Result\Result;

class Validation extends ValidationCore
{
    protected function msg(string $key, array $replace = []): ?string
    {
        $msgs = $this->msgs();
        if (!isset($msgs[$key])) {
            return null;
        }

        $msg = $msgs[$key];

        if ($replace) {
            $msg = str_replace(array_keys($replace), array_values($replace), $msg);
        }

        return $msg;
    }

    protected function msgs(): array
    {
        return [
            'callback' => 'Value is not valid',
            'digit' => 'Only digits allowed',
            'email' => 'value is not a valid email address',
            'max' => 'Value should be less than or equal {min}',
            'min' => 'Value should be greather than or equal {min}',
            'not_blank' => 'Value must not be blank',
            'regex' => 'Value is not valid',
            'required' => 'Value must not be blank',
        ];
    }

    protected function test(bool $assert, string $key, ?string $msg = null, array $replace = []): Result
    {
        return $assert
            ? Result::ofSuccess()
            : Result::ofErrorMsg(
                $msg !== null ? $msg : $this->msg($key, $replace),
                $key
            )
        ;
    }

    public function callback(callable $callback, string $msg = null): static
    {
        return $this->validator(fn(mixed $v) => $this->test($callback($v), 'callback', $msg));
    }

    public function digit(?string $msg = null): static
    {
        return $this->validator(fn (mixed $v) => $this->test(ctype_digit($v), 'digit', $msg));
    }

    public function email(?string $msg = null): static
    {
        return $this->validator(fn (mixed $v) => $this->test(filter_var($v, FILTER_VALIDATE_EMAIL) !== false, 'email', $msg));
    }

    public function min(int $min, string $msg = null): static
    {
        return $this->validator(fn (mixed $v) => $this->test($v >= $min, 'min', $msg, ['{min}' => $min]));
    }

    public function max(int $max, string $msg = null): static
    {
        return $this->validator(fn (mixed $v) => $this->test($v <= $max, 'max', $msg, ['{max}' => $max]));
    }

    public function notBlank(?string $msg = null, string $key = 'not_blank'): static
    {
        return $this->validator(
            fn (mixed $value) => $this->test(false !== $value && (!empty($value) || '0' == $value), $key, $msg)
        );
    }

    public function regex(string $regex, string $msg = null): static
    {
        return $this->validator(fn(mixed $v) => $this->test(preg_match($regex, $v), 'callback', $msg));
    }

    public function required(): static
    {
        $this->validator->required(true);
        return $this->notBlank(null, 'required');
    }
}
