<?php

declare(strict_types=1);

namespace Xtompie\Validation;

class MainTarget extends Target
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
