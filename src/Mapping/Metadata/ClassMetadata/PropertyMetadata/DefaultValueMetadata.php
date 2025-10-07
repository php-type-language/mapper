<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata;

use TypeLang\Mapper\Mapping\Metadata\Metadata;

final class DefaultValueMetadata extends Metadata
{
    public function __construct(
        public readonly mixed $value,
        ?int $createdAt = null,
    ) {
        parent::__construct($createdAt);
    }
}
