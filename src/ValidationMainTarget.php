<?php

declare(strict_types=1);

namespace Xtompie\Validation;

class ValidationMainTarget extends ValidationTarget
{
    public function __construct(
        protected ?string $space = null,
    ) {}

    protected function value(mixed $subject): mixed
    {
        return $subject;
    }

    protected function space(): ?string
    {
        return $this->space;
    }
}
