<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\Condition;

use TypeLang\Mapper\Mapping\Metadata\ConditionMetadata;

final class EmptyConditionMetadata extends ConditionMetadata
{
    public function match(object $object, mixed $value): bool
    {
        // @phpstan-ignore-next-line
        return empty($value);
    }
}
