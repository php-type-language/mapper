<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\ReaderException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Provider\MetadataProviderInterface;

/**
 * Provider overriding the metadata read by the lower readers with the
 * values it defines.
 */
interface MetadataReaderInterface extends MetadataProviderInterface
{
    /**
     * A type the reader cannot read is a type with an error, other wrong
     * values of one call are thrown together with the metadata read without
     * them.
     *
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassMetadata<TArg>|null $metadata metadata read by the lower
     *        readers, {@see null} in case of the reader is the first one
     * @return ClassMetadata<TArg>
     * @throws InvalidMetadataException in case of the reader finds wrong
     *         values
     * @throws ReaderException in case of the reader cannot be loaded
     */
    public function read(\ReflectionClass $class, ?ClassMetadata $metadata = null): ClassMetadata;
}
