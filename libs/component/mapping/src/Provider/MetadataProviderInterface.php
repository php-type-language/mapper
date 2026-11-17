<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Provider;

use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\ReaderException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

/**
 * Provides metadata of classes.
 */
interface MetadataProviderInterface
{
    /**
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @return ClassMetadata<TArg>
     * @throws InvalidMetadataException in case of the metadata has wrong
     *         values
     * @throws ReaderException in case of the metadata cannot be
     *         provided
     */
    public function read(\ReflectionClass $class): ClassMetadata;
}
