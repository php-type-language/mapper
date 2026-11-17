<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Check;

/**
 * Value is not an empty string and not an empty array.
 */
final class NonEmptyCheck implements CheckInterface
{
    /**
     * @var non-empty-string
     */
    public readonly string $description;

    public function __construct()
    {
        $this->description = 'non-empty';
    }

    public function isValid(mixed $value): bool
    {
        return $value !== '' && $value !== [];
    }
}
