<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\SkipCondition;

/**
 * The value of the property is {@see null}.
 */
final class NullSkipCondition implements SkipConditionInterface
{
    public function isMatched(object $object, mixed $value): bool
    {
        return $value === null;
    }
}
