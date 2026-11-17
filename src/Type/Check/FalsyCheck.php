<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Check;

/**
 * Value is a falsy one of PHP: an empty string, `'0'`, a zero, `false`.
 */
final class FalsyCheck implements CheckInterface
{
    /**
     * @var non-empty-string
     */
    public readonly string $description;

    public function __construct()
    {
        $this->description = 'falsy';
    }

    public function isValid(mixed $value): bool
    {
        return (bool) $value === false;
    }
}
