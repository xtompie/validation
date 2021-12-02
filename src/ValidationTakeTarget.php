<?php

declare(strict_types=1);

namespace Xtompie\Validation;

class ValidationTakeTarget extends ValidationTarget
{
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
}
