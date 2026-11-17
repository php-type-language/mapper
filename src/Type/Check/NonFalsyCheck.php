<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Check;

/**
 * Value is not a falsy one of PHP: not an empty string, not `'0'` and not a
 * zero.
 */
final class NonFalsyCheck implements CheckInterface
{
    /**
     * @var non-empty-string
     */
    public readonly string $description;

    public function __construct()
    {
        $this->description = 'non-falsy';
    }

    public function isValid(mixed $value): bool
    {
        return (bool) $value;
    }
}
