<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use TypeLang\Mapper\Mapping\Metadata\Struct\InputStructMetadata;
use TypeLang\Mapper\Mapping\Metadata\Struct\OutputStructMetadata;

/**
 * How a class looks in the data: the structure of its denormalization, the
 * structure of its normalization and its polymorphism.
 *
 * @template T of object
 */
final class ClassMetadata
{
    /**
     * Version of the format of the metadata classes of this namespace, it is
     * raised on each change of their properties, a stored metadata of
     * another version is not read
     */
    public const FORMAT_VERSION = 1;

    public function __construct(
        /**
         * @var class-string<T>
         */
        public readonly string $name,
        public readonly InputStructMetadata $input = new InputStructMetadata(),
        public readonly OutputStructMetadata $output = new OutputStructMetadata(),
        /**
         * Types the data of the class is mapped to by its tag, {@see null}
         * in case of the class is mapped by its own fields
         */
        public readonly ?DiscriminatorMetadata $discriminator = null,
    ) {}
}
