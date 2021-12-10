<?php

declare(strict_types=1);

namespace Xtompie\Validation;

use Xtompie\Result\Result;

class ValidationValidator
{
    protected array $groups = [];

    public function group(): ValidationGroup
    {
        if (!$this->groups) {
            $this->addGroup();
        }
        return array_values(array_slice($this->groups, -1))[0];
    }

    public function target(): ValidationTarget
    {
        return $this->group()->target();
    }

    public function addGroup()
    {
        $this->groups[] = new ValidationGroup();
    }

    public function addTarget(ValidationTarget $target)
    {
        return $this->group()->add($target);
    }

    public function method(string $name)
    {
        $this->addTarget(new ValidationTarget(
            fn (mixed $subject) => is_callable([$subject, $name]) ? $subject->{$name}() : null,
            $name
        ));
    }

    public function property(string $name)
    {
        $this->addTarget(new ValidationTarget(
            fn (mixed $subject) => is_object($subject) && isset($subject->$name) ? $subject->$name : null,
            $name
        ));
    }

    public function key(string $name)
    {
        $this->addTarget(new ValidationTarget(
            fn (mixed $subject) => is_array($subject) && array_key_exists($name, $subject) ? $subject[$name] : null,
            $name
        ));
    }

    public function take(callable $taker, $space = null)
    {
        $this->addTarget(new ValidationTarget($taker, $space));
    }

    public function main(string $space)
    {
        $this->addTarget(new ValidationTarget(
            fn (mixed $subject) => $subject,
            $space
        ));
    }

    /**
     * @return ValidationGroup[]
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
        foreach ($this->groups() as $group) {
            $result = $group->validate($subject);
            if ($result->fail()) {
                return $result;
            }
        }
        return Result::ofValue($subject);
    }
}
