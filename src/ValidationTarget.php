<?php

declare(strict_types=1);

namespace Xtompie\Validation;

use Xtompie\Result\Result;

class ValidationTarget
{
    protected $required = false;
    protected $filters = [];
    protected $validators = [];
    protected ?ValidationTarget $precedent = null;

    public function __construct(
        protected $take,
        protected ?string $space,
    ) {}

    public function value(mixed $subject): mixed
    {
        if ($this->precedent) {
            $subject = $this->precedent->value($subject);
        }
        return ($this->take)($subject);
    }

    public function space(): ?string
    {
        $prefix = null;
        if ($this->precedent) {
            $prefix = $this->precedent->space();
            if ($prefix) {
                $prefix = "$prefix.";
            }
        }
        return $prefix . $this->space;
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

    public function precedent(ValidationTarget $precedent)
    {
        $this->precedent = $precedent;
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
