<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Coercer;

/**
 * Converts a value to the target type when it is the same value written
 * in another form, such as `'42'` for `int`.
 *
 * @template-covariant T of int|float|string|bool|array<array-key, mixed>
 */
interface CoercerInterface
{
    /**
     * Returns the value converted to the target type, or {@see null} if
     * it cannot be converted without loss. A value that already has the
     * target type is returned as is. Never throws.
     *
     * @return T|null
     */
    public function coerce(mixed $value): mixed;
}
