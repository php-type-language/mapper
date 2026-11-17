<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Check;

/**
 * Constraint of a denormalized value, such as being non-empty. Constraints
 * are data of {@see \TypeLang\Mapper\Type\RefinedType}, readable to describe
 * the type, such as in a JSON Schema.
 *
 * ```
 * $check->isValid('');  // false for NonEmptyCheck
 * echo $check->description;    // non-empty
 * ```
 *
 * @property-read non-empty-string $description
 */
interface CheckInterface
{
    /**
     * Receives the value denormalized by the decorated type.
     */
    public function isValid(mixed $value): bool;
}
