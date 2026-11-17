<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Source;

use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\MetadataException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

/**
 * Errors found by one reader while reading one class.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class ErrorCollector
{
    /**
     * @var list<MetadataException>
     */
    private array $errors = [];

    public function addError(MetadataException $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * Throws all collected errors with the metadata read despite them, does
     * nothing in case of there are none.
     *
     * @template TArg of object
     * @param ClassMetadata<TArg> $metadata
     * @throws InvalidMetadataException in case of errors are collected
     */
    public function throwIfNotEmpty(ClassMetadata $metadata): void
    {
        if ($this->errors !== []) {
            throw InvalidMetadataException::becauseMetadataIsInvalid($metadata, $this->errors);
        }
    }
}
