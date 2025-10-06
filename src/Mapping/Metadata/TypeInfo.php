<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

final class TypeInfo
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $definition,
        public readonly ?SourceInfo $source = null,
    ) {}
}
