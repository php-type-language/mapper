<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\Struct;

use TypeLang\Mapper\Mapping\Metadata\Field\InputFieldMetadata;

/**
 * Structure of the data denormalized to a class.
 */
final class InputStructMetadata
{
    public function __construct(
        /**
         * In order of the keys of the data
         *
         * @var list<InputFieldMetadata>
         */
        public readonly array $fields = [],
        /**
         * Template of the message of a value that is not of the class as a
         * whole, {@see null} in case of the message of the mapper
         *
         * @var non-empty-string|null
         */
        public readonly ?string $typeErrorMessage = null,
    ) {}
}
