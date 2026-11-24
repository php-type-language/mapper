<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\Field;

use TypeLang\Mapper\Mapping\Metadata\Access\AccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;

/**
 * Field of the data of a class in one direction and the member of the class
 * it is bound to.
 */
abstract class FieldMetadata
{
    public function __construct(
        /**
         * Name of the field in the data, the naming strategy is applied
         *
         * @var non-empty-string
         */
        public readonly string $name,
        public readonly AccessMetadata $access,
        /**
         * Type of the value of the field, {@see null} in case of no source
         * declares it
         */
        public readonly ?TypeMetadata $type = null,
    ) {}
}
