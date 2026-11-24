<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\ReaderException;
use TypeLang\Mapper\Mapping\Reader\Reflection\ClassProperties;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;

/**
 * Reads metadata of a class by a chain of readers: each reader overrides
 * the values of the lower ones. The lowest reader is the base, such as
 * {@see ReflectionMetadataReader}, or the readers below the chain given its
 * metadata. Right after the base every {@see AccessSourceReaderInterface}
 * of the chain reads its accesses, from the lowest to the highest: the
 * other readers read the types at the chosen accesses. A nested chain is read
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
     * Readers of the accesses, from the lowest priority to the
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
     * Reads the base, then the accesses of the chain, then the other
     * readers. The base is the lowest reader of a chain read first, of a
     * chain given the metadata of the lower readers it is read already. The
     * next reader after a reader that throws {@see InvalidMetadataException}
     * gets the metadata of the exception.
     *
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassDraft<TArg>|null $metadata metadata the lowest reader gets
     * @return ClassDraft<TArg>
     * @throws InvalidMetadataException in case of the readers find wrong
     *         values
     * @throws ReaderException in case of a reader cannot be loaded
     */
    public function read(\ReflectionClass $class, ?ClassDraft $metadata = null): ClassDraft
    {
        $readers = $this->readers;
        $errors = new ErrorCollector();

        if ($metadata === null && $readers !== []) {
            $base = \array_shift($readers);
            $metadata = self::readSafely(static fn(): ClassDraft => $base->read($class), $errors);
        }

        $metadata ??= new ClassDraft($class->getName());

        foreach ($this->sources as $source) {
            $current = $metadata;
            $metadata = self::readSafely(static fn(): ClassDraft => $source->readAccess($class, $current), $errors);
        }

        $metadata = $metadata->withMemberOrder(self::getDeclarationOrder($class));

        foreach ($readers as $reader) {
            $current = $metadata;
            $metadata = self::readSafely(static fn(): ClassDraft => $reader->read($class, $current), $errors);
        }

        $errors->throwIfNotEmpty($metadata);

        /** @var ClassDraft<TArg> */
        return $metadata;
    }

    /**
     * Returns the keys of the members in order of declaration: the
     * properties, then the methods.
     *
     * @param \ReflectionClass<object> $class
     * @return list<non-empty-string>
     */
    private static function getDeclarationOrder(\ReflectionClass $class): array
    {
        $keys = [];

        foreach (ClassProperties::getProperties($class) as $property) {
            $key = ClassProperties::getKey($class, $property);

            if ($key !== '') {
                $keys[] = $key;
            }
        }

        foreach ($class->getMethods() as $method) {
            $keys[] = $method->name . '()';
        }

        return $keys;
    }

    /**
     * Runs the reading, the errors of the reader are collected, the metadata
     * read despite them are returned.
     *
     * @template TArg of object
     * @param \Closure(): ClassDraft<TArg> $read
     * @return ClassDraft<TArg>
     * @throws ReaderException in case of the reader cannot be loaded
     */
    private static function readSafely(\Closure $read, ErrorCollector $errors): ClassDraft
    {
        try {
            return $read();
        } catch (InvalidMetadataException $e) {
            foreach ($e->errors as $error) {
                $errors->addError($error);
            }

            /** @var ClassDraft<TArg> */
            return $e->metadata;
        }
    }
}
