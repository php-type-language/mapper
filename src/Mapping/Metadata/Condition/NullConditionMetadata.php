<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\Condition;

use TypeLang\Mapper\Mapping\Metadata\ConditionMetadata;

final class NullConditionMetadata extends ConditionMetadata
{
    public function match(object $object, mixed $value): bool
    {
        return $value === null;
    }
}
