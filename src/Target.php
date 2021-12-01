<?php

declare(strict_types=1);

namespace Xtompie\Validation;

use Xtompie\Result\Error;

abstract class Target
{
    protected $required = false;
    protected $filters = [];
    protected $validators = [];

    abstract protected function value(mixed $subject): mixed;
    abstract protected function space(): ?string;

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

    public function validate(mixed $subject): ?Error
    {
        $value = $this->value($subject);

        if (!$this->required && $this->isBlank($value)) {
            return null;
        }

        foreach ($this->filters as $filter) {
            $value = $filter($value);
        }

        foreach ($this->validators as $validator) {
            $error = $validator($value);
            if ($error instanceof Error) {
                return $error->withSpace($this->space());
            }
        }

        return null;
    }

    protected function isBlank($value): bool
    {
        return (false === $value || (empty($value) && '0' != $value));
    }
}
