<?php

declare(strict_types=1);

namespace Xtompie\Validation;

class ValidationKeyTarget extends ValidationTarget
{
    public function __construct(
        protected string $key,
    ) {}

    protected function value(mixed $subject): mixed
    {
        if (!is_array($subject) || !array_key_exists($this->key, $subject)) {
            return null;
        }
        return $subject[$this->key];
    }

    protected function space(): ?string
    {
        return $this->key;
    }
}
