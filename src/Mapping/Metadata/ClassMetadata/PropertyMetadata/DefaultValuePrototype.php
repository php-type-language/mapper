<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata;

final class DefaultValuePrototype
{
    public function __construct(
        public readonly mixed $value,
    ) {}
}
