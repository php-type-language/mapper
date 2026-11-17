<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\SkipCondition;

/**
 * The value of the property is empty by the rules of PHP `empty()`: `null`,
 * `false`, `0`, `0.0`, `''`, `'0'` or `[]`.
 */
final class EmptySkipCondition implements SkipConditionInterface
{
    public function isMatched(object $object, mixed $value): bool
    {
        return $value === null
            || $value === false
            || $value === 0
            || $value === 0.0
            || $value === ''
            || $value === '0'
            || $value === [];
    }
}
