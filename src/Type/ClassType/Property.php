<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ClassType;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * Mapped property of a class in one direction: its metadata and the type
 * of its value built from them once.
 *
 * @template TValue
 */
final class Property
{
    public function __construct(
        public readonly PropertyMetadata $metadata,
        /**
         * Type of the written value for a property of the denormalization,
         * of the read value for a property of the normalization
         *
         * @var TypeInterface<TValue>
         */
        public readonly TypeInterface $type,
    ) {}
}
