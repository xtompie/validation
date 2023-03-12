<?php

declare(strict_types=1);

namespace Xtompie\Validation;

use Xtompie\Result\Result;

class ValidationGroup
{
    /**
     * @param ValidationTarget[] $targets
     * @param ValidationTarget[] $nesteds
     */
    public function __construct(
        protected array $targets = [],
        protected array $nesteds =[],
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
        if ($this->nesteds) {
            $target->precedent($this->nesteds[count($this->nesteds)-1]);
        }
        $this->targets[] = $target;
    }

    public function nested(): void
    {
        $this->nesteds[] = $this->target();
    }

    public function unested(): void
    {
        array_pop($this->nesteds);
    }

    public function resetNested(): void
    {
        $this->nesteds = [];
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
