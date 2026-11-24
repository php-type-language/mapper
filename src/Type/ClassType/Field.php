<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ClassType;

use TypeLang\Mapper\Mapping\Metadata\Field\FieldMetadata;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\TypeNode;

/**
 * Mapped field of a class in one direction: its metadata and the type of
 * its value built from them once.
 *
 * @template TField of FieldMetadata
 * @template TValue
 */
final class Field
{
    public function __construct(
        /**
         * @var TField
         */
        public readonly FieldMetadata $metadata,
        /**
         * Position of the field in the structure of the metadata, the key
         * of its reader or writer
         *
         * @var int<0, max>
         */
        public readonly int $index,
        /**
         * Type of the written value for a field of the denormalization, of
         * the read value for a field of the normalization
         *
         * @var TypeInterface<TValue>
         */
        public readonly TypeInterface $type,
        /**
         * Type of the value as it is declared, the expected type of the
         * violations of the value
         */
        public readonly TypeNode $declared,
    ) {}
}
