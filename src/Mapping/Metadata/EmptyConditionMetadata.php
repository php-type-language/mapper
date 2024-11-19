<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

final class EmptyConditionMetadata extends MatchConditionMetadata
{
    public function match(object $object, mixed $value): bool
    {
        // @phpstan-ignore-next-line
        return empty($value);
    }
}
