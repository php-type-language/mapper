<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\ReaderException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;

/**
 * Reads metadata of a class by a chain of readers: each reader overrides
 * the values of the lower ones. The lowest reader is the base, such as
 * {@see ReflectionMetadataReader}, or the readers below the chain given its
 * metadata. Right after the base every {@see AccessSourceReaderInterface}
 * of the chain reads its access points, from the lowest to the highest: the
 * other readers read the types at the chosen points. A nested chain is read
 * as a part of this one. Errors of all readers are thrown together with the
 * metadata read despite them.
 */
final class ChainMetadataReader implements MetadataReaderInterface
{
    /**
     * From the lowest priority to the highest one, without nested chains
     *
     * @var list<MetadataReaderInterface>
     */
    private readonly array $readers;

    /**
     * Readers of the access points, from the lowest priority to the
     * highest one
     *
     * @var list<AccessSourceReaderInterface>
     */
    private readonly array $sources;

    /**
     * @param iterable<array-key, MetadataReaderInterface> $readers from the
     *        highest priority to the lowest one
     */
    public function __construct(iterable $readers)
    {
        $result = [];

        foreach ($readers as $reader) {
            if (!$reader instanceof self) {
                $result[] = $reader;

                continue;
            }

            foreach (\array_reverse($reader->readers) as $nested) {
                $result[] = $nested;
            }
        }

        $this->readers = \array_reverse($result);
        $this->sources = \array_values(\array_filter(
            $this->readers,
            static fn(MetadataReaderInterface $reader): bool => $reader instanceof AccessSourceReaderInterface,
        ));
    }

    /**
     * Reads the base, then the access points of the chain, then the other
     * readers. The base is the lowest reader of a chain read first, of a
     * chain given the metadata of the lower readers it is read already. The
     * next reader after a reader that throws {@see InvalidMetadataException}
     * gets the metadata of the exception.
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
        $readers = $this->readers;
        $errors = new ErrorCollector();

        if ($metadata === null && $readers !== []) {
            $base = \array_shift($readers);
            $metadata = self::readSafely(static fn(): ClassMetadata => $base->read($class), $errors);
        }

        $metadata ??= new ClassMetadata($class->getName());

        foreach ($this->sources as $source) {
            $current = $metadata;
            $metadata = self::readSafely(static fn(): ClassMetadata => $source->readAccess($class, $current), $errors);
        }

        foreach ($readers as $reader) {
            $current = $metadata;
            $metadata = self::readSafely(static fn(): ClassMetadata => $reader->read($class, $current), $errors);
        }

        $errors->throwIfNotEmpty($metadata);

        /** @var ClassMetadata<TArg> */
        return $metadata;
    }

    /**
     * Runs the reading, the errors of the reader are collected, the metadata
     * read despite them are returned.
     *
     * @template TArg of object
     * @param \Closure(): ClassMetadata<TArg> $read
     * @return ClassMetadata<TArg>
     * @throws ReaderException in case of the reader cannot be loaded
     */
    private static function readSafely(\Closure $read, ErrorCollector $errors): ClassMetadata
    {
        try {
            return $read();
        } catch (InvalidMetadataException $e) {
            foreach ($e->errors as $error) {
                $errors->addError($error);
            }

            /** @var ClassMetadata<TArg> */
            return $e->metadata;
        }
    }
}
