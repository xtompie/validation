<?php

declare(strict_types=1);

namespace Xtompie\Validation;

class ValidationMethodTarget extends ValidationTarget
{
    public function __construct(
        protected string $method,
    ) {}

    protected function value(mixed $subject): mixed
    {
        if (!is_callable([$subject, $this->method])) {
            return null;
        }
        return $subject->{$this->method}();
    }

    protected function space(): ?string
    {
        return $this->method;
    }
}
