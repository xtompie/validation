<?php

declare(strict_types=1);

namespace Xtompie\Validation;

use Xtompie\Result\Error;
use Xtompie\Result\ErrorCollection;
use Xtompie\Result\Result;

class ValidationCore
{
    protected ValidationValidator $validator;
    protected mixed $subject;

    public static function of(mixed $subject): static
    {
        return new static($subject);
    }

    public static function new(): static
    {
        return new static();
    }

    public function __construct(mixed $subject = null)
    {
        $this->subject = $subject;
        $this->validator = new ValidationValidator();
    }

    public function subject(mixed $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function group(): static
    {
        $this->validator->addGroup();
        return $this;
    }

    public function method(string $name): static
    {
        $this->validator->addTarget(new MethodTarget($name));
        return $this;
    }

    public function property(string $name): static
    {
        $this->validator->addTarget(new PropertyTarget($name));
        return $this;
    }

    public function key(string $name): static
    {
        $this->validator->addTarget(new KeyTarget($name));
        return $this;
    }

    public function take(callable $taker, $space = null): static
    {
        $this->validator->addTarget(new TakeTarget($taker, $space));
        return $this;
    }

    public function main(string $space): static
    {
        $this->validator->addTarget(new MainTarget($space));
        return $this;
    }

    public function optional(): static
    {
        $this->validator->required(false);
        return $this;
    }

    public function required(): static
    {
        $this->validator->required(true);
        return $this;
    }

    /**
     * @param callable $validator `(mixed $value): ?App\Core\Error` null on success otherwise Error
     * @return static
     */
    public function validator(callable $validator): static
    {
        $this->validator->validator($validator);
        return $this;
    }

    /**
     * @param callable $filter `(mixed $value): mixed`
     * @return static
     */
    public function filter(callable $filter): static
    {
        $this->validator->filter($filter);
        return $this;
    }

    public function result(): Result
    {
        return $this->validator->validate($this->subject);
    }

    public function errors(): ErrorCollection
    {
        return $this->result()->errors();
    }

    public function error(): ?Error
    {
        return $this->errors()->first();
    }

    public function fail(): bool
    {
        return $this->result()->fail();
    }

    public function success(): bool
    {
        return $this->result()->success();
    }
}
