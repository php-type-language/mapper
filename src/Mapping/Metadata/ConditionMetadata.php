<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

abstract class ConditionMetadata extends Metadata
{
    abstract public function match(object $object, mixed $value): bool;
}
