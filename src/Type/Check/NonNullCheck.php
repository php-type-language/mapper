<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Check;

/**
 * Value is not `null`.
 */
final class NonNullCheck implements CheckInterface
{
    /**
     * @var non-empty-string
     */
    public readonly string $description;

    public function __construct()
    {
        $this->description = 'non-null';
    }

    public function isValid(mixed $value): bool
    {
        return $value !== null;
    }
}
