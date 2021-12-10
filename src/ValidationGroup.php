<?php

declare(strict_types=1);

namespace Xtompie\Validation;

use Xtompie\Result\Result;

class ValidationGroup
{
    public function __construct(
        protected array $targets = [],
    ) {}

    public function target(): ValidationTarget
    {
        if (!$this->targets) {
            $this->targets[] = new ValidationTarget(fn (mixed $subject) => $subject, null);
        }
        return array_values(array_slice($this->targets, -1))[0];
    }

    public function add(ValidationTarget $target)
    {
        $this->targets[] = $target;
    }

    public function validate(mixed $subject): Result
    {
        return array_reduce(
            $this->targets,
            fn(Result $carry, ValidationTarget $item) => Result::ofCombine($carry, $item->validate($subject)),
            Result::ofSuccess()
        );
    }
}
