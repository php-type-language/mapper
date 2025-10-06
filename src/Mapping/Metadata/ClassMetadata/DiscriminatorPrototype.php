<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\DiscriminatorMetadata\DiscriminatorMapPrototype;
use TypeLang\Mapper\Mapping\Metadata\TypePrototype;

final class DiscriminatorPrototype
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $field,
        public readonly DiscriminatorMapPrototype $map = new DiscriminatorMapPrototype(),
        public ?TypePrototype $default = null,
    ) {}
}
