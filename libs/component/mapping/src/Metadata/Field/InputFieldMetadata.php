<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\Field;

use TypeLang\Mapper\Mapping\Metadata\Access\AccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\AliasesMetadata;
use TypeLang\Mapper\Mapping\Metadata\DefaultValueMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;

/**
 * Field of the data denormalized to a class.
 */
final class InputFieldMetadata extends FieldMetadata
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        string $name,
        AccessMetadata $access,
        ?TypeMetadata $type = null,
        /**
         * Other names of the field, {@see null} in case of the field has
         * only its name
         */
        public readonly ?AliasesMetadata $aliases = null,
        /**
         * Whether the value is taken without coercion, {@see null} in case
         * of the configuration of the mapper
         */
        public readonly ?bool $strict = null,
        /**
         * Value written in case of the field is absent from the data,
         * {@see null} in case of nothing is written
         */
        public readonly ?DefaultValueMetadata $default = null,
        /**
         * Whether the data may have no field: with a default value it is
         * written, without it the member is not accessed, such as a setter
         * with an optional parameter
         */
        public readonly bool $isOptional = false,
        /**
         * Template of the message of a value of a wrong type, {@see null}
         * in case of the message of the mapper
         *
         * @var non-empty-string|null
         */
        public readonly ?string $typeErrorMessage = null,
        /**
         * Template of the message of an absent field, {@see null} in case
         * of the message of the mapper
         *
         * @var non-empty-string|null
         */
        public readonly ?string $undefinedErrorMessage = null,
    ) {
        parent::__construct($name, $access, $type);
    }
}
