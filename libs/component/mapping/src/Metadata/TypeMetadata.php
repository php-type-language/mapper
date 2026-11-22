<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use TypeLang\Type\TypeNode;

/**
 * Type with the source that defined it: a type tree or the reason the
 * source cannot read the type.
 */
final class TypeMetadata
{
    private function __construct(
        /**
         * Type with the imported names and the names of the classes of the
         * namespace of the file resolved, read-only by contract, {@see null}
         * in case of the type cannot be read
         */
        public readonly ?TypeNode $type,
        /**
         * Reason the type cannot be read, {@see null} in case of the type
         * is read
         *
         * @var non-empty-string|null
         */
        public readonly ?string $error,
        public readonly SourceInfo $source,
    ) {}

    public static function createFromType(TypeNode $type, SourceInfo $source): self
    {
        return new self(
            type: $type,
            error: null,
            source: $source,
        );
    }

    /**
     * @param non-empty-string $error reason the type cannot be read
     */
    public static function createFromError(string $error, SourceInfo $source): self
    {
        return new self(
            type: null,
            error: $error,
            source: $source,
        );
    }
}
