<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\ReaderException;

/**
 * Overrides the draft of the metadata read by the lower readers with the
 * values it defines.
 */
interface MetadataReaderInterface
{
    /**
     * A type the reader cannot read is a type with an error, other wrong
     * values of one call are thrown together with the metadata read without
     * them.
     *
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassDraft<TArg>|null $metadata metadata read by the lower
     *        readers, {@see null} in case of the reader is the first one
     * @return ClassDraft<TArg>
     * @throws InvalidMetadataException in case of the reader finds wrong
     *         values
     * @throws ReaderException in case of the reader cannot be loaded
     */
    public function read(\ReflectionClass $class, ?ClassDraft $metadata = null): ClassDraft;
}
