<?php

declare(strict_types=1);

namespace Xtompie\Validation;

use Xtompie\Result\Error;
use Xtompie\Result\ErrorCollection;

class Group
{
    public function __construct(
        protected array $targets = [],
    ) {}

    public function target(): Target
    {
        if (!$this->targets) {
            $this->targets[] = new MainTarget();
        }
        return array_values(array_slice($this->targets, -1))[0];
    }

    public function add(Target $target)
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
