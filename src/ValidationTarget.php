<?php

declare(strict_types=1);

namespace Xtompie\Validation;

use Xtompie\Result\Result;

class ValidationTarget
{
    protected $required = false;
    protected $filters = [];
    protected $validators = [];

    public function __construct(
        protected $take,
        protected ?string $space,
    ) {}

    protected function value(mixed $subject): mixed
    {
        return ($this->take)($subject);
    }

    protected function space(): ?string
    {
        return $this->space;
    }

    public function required(bool $required)
    {
        $this->required = $required;
    }

    public function filter(callable $filter)
    {
        $this->filters[] = $filter;
    }

    public function validator(callable $validator)
    {
        $this->validators[] = $validator;
    }

    public function validate(mixed $subject): Result
    {
        $value = $this->value($subject);

        if (!$this->required && $this->isBlank($value)) {
            return Result::ofSuccess();
        }

        foreach ($this->filters as $filter) {
            $value = $filter($value);
        }

        foreach ($this->validators as $validator) {
            /** @var Result $result */
            $result = $validator($value);
            if ($result->fail()) {
                return Result::ofErrors(
                    $this->space() !== null
                        ? $result->errors()->withPrefix($this->space())
                        : $result->errors()
                );
            }
        }

        return Result::ofSuccess();
    }

    protected function isBlank($value): bool
    {
        return (false === $value || (empty($value) && '0' != $value));
    }
}
