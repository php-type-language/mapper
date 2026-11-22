<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\ReaderException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

/**
 * Reader of values that include the access points of the properties, such
 * as the attribute and the config readers. {@see ChainMetadataReader} reads
 * the access points of such readers right after its lowest reader, before
 * all the type readers: they read the types at the chosen points.
 */
interface AccessSourceReaderInterface extends MetadataReaderInterface
{
    /**
     * Reads the access points only, the other values and the errors of the
     * structure of the source are read by {@see self::read()}.
     *
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassMetadata<TArg> $metadata
     * @return ClassMetadata<TArg>
     * @throws InvalidMetadataException in case of an access point is wrong
     * @throws ReaderException in case of the source cannot be read
     */
    public function readAccess(\ReflectionClass $class, ClassMetadata $metadata): ClassMetadata;
}
