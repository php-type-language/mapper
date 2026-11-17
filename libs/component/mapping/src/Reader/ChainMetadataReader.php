<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\ReaderException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

/**
 * Reads metadata of a class by a chain of readers: each reader overrides
 * the values of the lower ones. Errors of all readers are thrown together
 * with the metadata read despite them.
 */
final class ChainMetadataReader implements MetadataReaderInterface
{
    /**
     * @var list<MetadataReaderInterface>
     */
    private readonly array $readers;

    /**
     * @param iterable<array-key, MetadataReaderInterface> $readers from the
     *        highest priority to the lowest one
     */
    public function __construct(iterable $readers)
    {
        $result = [];

        foreach ($readers as $reader) {
            $result[] = $reader;
        }

        $this->readers = \array_reverse($result);
    }

    /**
     * The next reader after a reader that throws
     * {@see InvalidMetadataException} gets the metadata of the exception.
     *
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassMetadata<TArg>|null $metadata metadata the lowest reader gets
     * @return ClassMetadata<TArg>
     * @throws InvalidMetadataException in case of the readers find wrong
     *         values
     * @throws ReaderException in case of a reader cannot be loaded
     */
    public function read(\ReflectionClass $class, ?ClassMetadata $metadata = null): ClassMetadata
    {
        $errors = [];

        foreach ($this->readers as $reader) {
            try {
                $metadata = $reader->read($class, $metadata);
            } catch (InvalidMetadataException $e) {
                /** @var ClassMetadata<TArg> */
                $metadata = $e->metadata;
                $errors = [...$errors, ...$e->errors];
            }
        }

        $metadata ??= new ClassMetadata($class->getName());

        if ($errors !== []) {
            throw InvalidMetadataException::becauseMetadataIsInvalid($metadata, $errors);
        }

        return $metadata;
    }
}
