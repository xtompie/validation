<?php

declare(strict_types=1);

namespace Xtompie\Validation;

use Xtompie\Result\Error;
use Xtompie\Result\ErrorCollection;
use Xtompie\Result\Result;

class Validation extends ValidationCore
{
    protected function msgs(): array
    {
        return [
            'array' => 'Value must be of array type',
            'array_of_strings' => 'Value must be an array of strings',
            'alnum' => 'Only alphanumeric allowed',
            'alpha' => 'Only alphabetic allowed',
            'callback' => 'Value is not valid',
            'choice' => 'Value must be a one of {choices}.',
            'choice_multi' => 'Values {{values}} must be a one of: {{choices}}',
            'date' => 'Date must be in {format} format',
            'digit' => 'Only digits allowed',
            'email' => 'value is not a valid email address',
            'length' => 'Value should  have exacly {length} characters',
            'length_min' => 'Value should have {min} characters or more',
            'length_max' => 'Value should have {max} characters or less',
            'max' => 'Value should be less than or equal {min}',
            'min' => 'Value should be greather than or equal {min}',
            'not_blank' => 'Value must not be blank',
            'only' => 'Invalid key: {key}',
            'regex' => 'Value is not valid',
            'required' => 'Value must not be blank',
            'string' => 'Value must be of string type',
        ];
    }

    protected function msg(string $key): ?string
    {
        $msgs = $this->msgs();
        return $msgs[$key] ?? null;
    }

    protected function errormsg(string $key, array $replace = [], ?string $msg = null): ?string
    {
        $msg = $msg ?? $this->msg($key);

        if ($replace) {
            $msg = str_replace(array_keys($replace), array_values($replace), $msg);
        }

        return $msg;
    }

    public function callback(callable $callback, string $msg = null, string $key = 'callback'): static
    {
        return $this->validator(fn(mixed $v) => $this->test($callback($v), $key, $msg));
    }

    public function pipe(callable $pipe): static
    {
        return $pipe($this);
    }

    public function when(callable $if, callable $then): static
    {
        return $this->pipe(fn (Validation $v) => $if($v->subject()) ? $then($v) : $v);
    }

    public function each(callable $item): static
    {
        $main = $this;
        $main = $main->nested();
        foreach (array_keys((array)$main->targetSubject()) as $offset) {
            $main = $main->key((string)$offset)->nested()->pipe($item)->unested();
        }
        $main = $main->unested();
        return $main;
    }

    protected function test(bool $assert, string $key, ?string $msg = null, array $replace = []): Result
    {
        return $assert ? Result::ofSuccess() : Result::ofErrorMsg($this->errormsg($key, $replace, $msg), $key);
    }

    public function array($msg = null): static
    {
        return $this->validator(fn($v) => $this->test(is_array($v), 'array', $msg));
    }

    public function arrayOfStrings($msg = null): static
    {
        return $this->validator(fn($v) => $this->test(
            is_array($v) && count(array_filter($v, fn ($i) => !is_string($i))) === 0,
            'array_of_strings',
            $msg
        ));
    }

    public function alnum(?string $msg = null): static
    {
        return $this->validator(fn (mixed $v) => $this->test(ctype_alnum($v), 'alnum', $msg));
    }

    public function alpha(?string $msg = null): static
    {
        return $this->validator(fn (mixed $v) => $this->test(ctype_alpha($v), 'alpha', $msg));
    }

    public function choice(array $choices, ?string $msg = null): static
    {
        return $this->validator(fn($v) => $this->test(in_array($v, $choices), 'choice', $msg, [
            '{choices}' => '\'' . implode('\', \'', $choices) . '\''
        ]));
    }

    public function choiceMulti(array $choices, ?string $msg = null): static
    {
        return $this->validator(function(array $values) use ($choices, $msg) {
            $invalids = array_filter($values, fn ($value) => !in_array($value, $choices));
            return !$invalids ? Result::ofSuccess() : Result::ofErrorMsg(
                $this->errormsg('choice_multi', ['{values}' => implode(', ', $invalids), '{choices}' => implode(', ', $choices)], $msg),
                'choice_multi',
            );
        });
    }

    public function date(string $format, ?string $msg = null): static
    {
        return $this->validator(function (mixed $v) use ($format, $msg) {
            $validator = new DateValidator($format);
            return $validator->__invoke((string)$v)
                ? Result::ofSuccess()
                : Result::ofErrorMsg($this->errormsg('date', ['{format}' => $validator->hrFormat()], $msg), 'date')
            ;
        });
    }

    public function digit(?string $msg = null): static
    {
        return $this->validator(fn (mixed $v) => $this->test(ctype_digit($v), 'digit', $msg));
    }

    public function email(?string $msg = null): static
    {
        return $this->validator(fn (mixed $v) => $this->test(filter_var($v, FILTER_VALIDATE_EMAIL) !== false, 'email', $msg));
    }

    public function length(int $length, ?string $msg = null): static
    {
        return $this->validator(fn (mixed $v) => $this->test(strlen((string)$v) == $length, 'length', $msg, ['{length}' => $length]));
    }

    public function lengthMin(int $min, ?string $msg = null): static
    {
        return $this->validator(fn (mixed $v) => $this->test(strlen((string) $v) >= $min, 'length_min', $msg, ['{min}' => $min]));
    }

    public function lengthMax(int $max, ?string $msg = null): static
    {
        return $this->validator(fn (mixed $v) => $this->test(strlen((string) $v) <= $max, 'length_max', $msg, ['{max}' => $max]));
    }

    public function min(int $min, ?string $msg = null): static
    {
        return $this->validator(fn (mixed $v) => $this->test($v >= $min, 'min', $msg, ['{min}' => $min]));
    }

    public function max(int $max, ?string $msg = null): static
    {
        return $this->validator(fn (mixed $v) => $this->test($v <= $max, 'max', $msg, ['{max}' => $max]));
    }

    public function notBlank(?string $msg = null, string $key = 'not_blank'): static
    {
        return $this->validator(
            fn (mixed $value) => $this->test(false !== $value && (!empty($value) || '0' == $value), $key, $msg)
        );
    }

    public function only(array $keys, ?string $msg = null): static
    {
        return $this->validator(function(array $array) use ($keys, $msg) {
            $invalid = array_filter(array_keys($array), fn ($key) => !in_array($key, $keys));
            return !$invalid ? Result::ofSuccess() : Result::ofErrors(new ErrorCollection(
                array_map(
                    fn ($key) => Error::of($this->errormsg('only', ['{key}' => $key], $msg), 'only', $key),
                    $invalid
                )
            ));
        });
    }

    public function regex(string $regex, ?string $msg = null): static
    {
        return $this->validator(fn(mixed $v) => $this->test(preg_match($regex, $v) === 1, 'callback', $msg));
    }

    public function required(?string $msg = null, string $key = 'required'): static
    {
        $this->validator->required(true);
        return $this->notBlank($msg, $key);
    }

    public function string($msg = null): static
    {
        return $this->validator(fn($v) => $this->test(is_string($v), 'string', $msg));
    }
}
