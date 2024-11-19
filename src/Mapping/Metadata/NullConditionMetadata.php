<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

final class NullConditionMetadata extends MatchConditionMetadata
{
    public function match(object $object, mixed $value): bool
    {
        return $value === null;
    }
}
