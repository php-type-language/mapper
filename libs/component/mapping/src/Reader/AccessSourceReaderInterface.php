<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\ReaderException;

/**
 * Reader of values that include the accesses of the properties, such
 * as the attribute and the config readers. {@see ChainMetadataReader} reads
 * the accesses of such readers right after its lowest reader, before
 * all the type readers: they read the types at the chosen accesses.
 */
interface AccessSourceReaderInterface extends MetadataReaderInterface
{
    /**
     * Reads the accesses only, the other values and the errors of the
     * structure of the source are read by {@see self::read()}.
     *
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassDraft<TArg> $metadata
     * @return ClassDraft<TArg>
     * @throws InvalidMetadataException in case of an access is wrong
     * @throws ReaderException in case of the source cannot be read
     */
    public function readAccess(\ReflectionClass $class, ClassDraft $metadata): ClassDraft;
}
