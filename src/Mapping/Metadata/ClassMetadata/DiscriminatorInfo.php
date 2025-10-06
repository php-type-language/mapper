<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

use TypeLang\Mapper\Mapping\Metadata\TypeInfo;

final class DiscriminatorInfo
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $field,
        /**
         * @var non-empty-array<non-empty-string, TypeInfo>
         */
        public array $map,
        public ?TypeInfo $default = null,
    ) {}
}
