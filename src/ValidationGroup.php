<?php

declare(strict_types=1);

namespace Xtompie\Validation;

use Xtompie\Result\Error;
use Xtompie\Result\ErrorCollection;

class ValidationGroup
{
    public function __construct(
        protected array $targets = [],
    ) {}

    public function target(): ValidationTarget
    {
        if (!$this->targets) {
            $this->targets[] = new ValidationMainTarget();
        }
        return array_values(array_slice($this->targets, -1))[0];
    }

    public function add(ValidationTarget $target)
    {
        $this->targets[] = $target;
    }

    public function validate(mixed $subject): ErrorCollection
    {
        $errors = ErrorCollection::ofEmpty();
        foreach ($this->targets as $target) {
            $error = $target->validate($subject);
            if ($error instanceof Error) {
                $errors = $errors->add($error);
            }
        }
        return $errors;
    }
}
