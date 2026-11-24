<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

/**
 * Other names of a field accepted on denormalization and the rule taking
 * one of the names present in the data.
 */
final class AliasesMetadata
{
    public function __construct(
        /**
         * @var non-empty-list<non-empty-string>
         */
        public readonly array $names,
        public readonly AliasConflict $conflict = AliasConflict::DEFAULT,
    ) {}
}
