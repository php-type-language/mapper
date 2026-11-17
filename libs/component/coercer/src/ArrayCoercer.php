<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Coercer;

/**
 * Converts a {@see \stdClass} object to an array of its properties.
 *
 * Subclasses of {@see \stdClass} are not converted.
 *
 * @template-implements CoercerInterface<array<array-key, mixed>>
 */
final class ArrayCoercer implements CoercerInterface
{
    /**
     * @return array<array-key, mixed>|null
     */
    public function coerce(mixed $value): ?array
    {
        if (\is_array($value)) {
            return $value;
        }

        if (\is_object($value) && $value::class === \stdClass::class) {
            return (array) $value;
        }

        return null;
    }
}
