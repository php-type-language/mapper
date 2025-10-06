<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

final class TypePrototype
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $definition,
        public readonly ?SourceMapPrototype $source = null,
    ) {}
}
