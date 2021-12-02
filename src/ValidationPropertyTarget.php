<?php

declare(strict_types=1);

namespace Xtompie\Validation;

class ValidationPropertyTarget extends ValidationTarget
{
    public function __construct(
        protected string $property,
    ) {}

    protected function value(mixed $subject): mixed
    {
        if (!is_object($subject) && !isset($subject->{$this->property})) {
            return null;
        }
        return $subject->{$this->property};
    }

    protected function space(): ?string
    {
        return $this->property;
    }
}
