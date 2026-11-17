<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Check;

/**
 * String of a number, as {@see \is_numeric()} accepts it: `42`, `-1.5`,
 * `1e3`, ` 42` with leading or trailing whitespace.
 */
final class NumericCheck implements CheckInterface
{
    /**
     * @var non-empty-string
     */
    public readonly string $description;

    public function __construct()
    {
        $this->description = 'numeric';
    }

    public function isValid(mixed $value): bool
    {
        return \is_string($value) && \is_numeric($value);
    }
}
