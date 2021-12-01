<?php

declare(strict_types=1);

namespace Xtompie\Validation;

use Xtompie\Result\ErrorCollection;
use Xtompie\Result\Result;

class ValidationValidator
{
    protected array $groups = [];

    public function group(): Group
    {
        if (!$this->groups) {
            $this->addGroup();
        }
        return array_values(array_slice($this->groups, -1))[0];
    }

    public function target(): Target
    {
        return $this->group()->target();
    }

    public function addGroup()
    {
        $this->groups[] = new Group();
    }

    public function addTarget(Target $target)
    {
        return $this->group()->add($target);
    }

    /**
     * @return Group[]
     */
    public function groups(): array
    {
        return $this->groups;
    }

    public function required(bool $required)
    {
        $this->target()->required($required);
    }

    public function optional(bool $required)
    {
        $this->target()->required($required);
    }

    public function filter(callable $filter)
    {
        $this->target()->filter($filter);
    }

    public function validator(callable $validator)
    {
        $this->target()->validator($validator);
    }

    public function validate(mixed $subject): Result
    {
        foreach ($this->groups as $group) {
            $errors = $group->validate($subject);
            if ($errors->any()) {
                return Result::ofErrors($errors);
            }
        }
        return Result::ofValue($subject);
    }
}
