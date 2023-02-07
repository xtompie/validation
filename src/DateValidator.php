<?php

declare(strict_types=1);

namespace Xtompie\Validation;

class DateValidator
{
    protected $patterns = [
        'Y' => '[0-9]{4}',
        'm' => '[01]{1}[0-9]{1}',
        'd' => '[0-3]{1}[0-9]{1}',
        'H' => '[0-2]{1}[0-9]{1}',
        'i' => '[0-5]{1}[0-9]{1}',
        's' => '[0-5]{1}[0-9]{1}',
    ];

    public function __construct(
        protected string $format = 'Y.m.d H:i:s',
        protected array $hr = [
            'Y' => 'YYYY',
            'm' => 'MM',
            'd' => 'DD',
            'H' => 'HH',
            'i' => 'MM',
            's' => 'SS',
        ],
    ) {
    }

    public function withFormat(string $format): static
    {
        $new = clone $this;
        $new->format = $format;
        return $new;
    }

    public function withHr(array $hr): static
    {
        $new = clone $this;
        $new->hr = $hr;
        return $new;
    }

    public function format(): string
    {
        return $this->format;
    }

    public function hrFormat(): string
    {
        return str_replace(array_keys($this->hr), array_values($this->hr), $this->format());
    }

    protected function pattern(): string
    {
        $pattern = '';
        $delimiter = '#';

        for ($i = 0, $end = strlen($this->format); $i < $end; $i++) {
            $char = $this->format[$i];
            $pattern .= isset($this->patterns[$char])
                ? '(?P<' . preg_quote($char, $delimiter). '>' . $this->patterns[$char] . ')'
                : preg_quote($char, $delimiter);
        }

        $pattern = $delimiter. '^' . $pattern . '$' . $delimiter;

        return $pattern;
    }

    public function __invoke(string $value): bool
    {
        return 1 === preg_match($this->pattern(), $value, $match);
    }
}
