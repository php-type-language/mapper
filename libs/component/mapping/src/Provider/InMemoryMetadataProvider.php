<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Provider;

use TypeLang\Mapper\Mapping\Exception\ReaderException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\EnumMetadata;

/**
 * Keeps metadata provided by the delegate for the lifetime of the process.
 */
final class InMemoryMetadataProvider implements MetadataProviderInterface
{
    /**
     * @var array<class-string, ClassMetadata<covariant object>>
     */
    private array $metadata = [];

    /**
     * @var array<class-string, EnumMetadata<covariant \UnitEnum>>
     */
    private array $enums = [];

    public function __construct(
        private readonly MetadataProviderInterface $delegate,
    ) {}

    /**
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @return ClassMetadata<TArg>
     * @throws ReaderException in case of the delegate cannot
     *         provide the metadata
     */
    public function read(\ReflectionClass $class): ClassMetadata
    {
        /** @var ClassMetadata<TArg> */
        return $this->metadata[$class->name] ??= $this->delegate->read($class);
    }

    /**
     * @template TArg of \UnitEnum
     * @param \ReflectionEnum<TArg> $enum
     * @return EnumMetadata<TArg>
     * @throws ReaderException in case of the delegate cannot
     *         provide the metadata
     */
    public function readEnum(\ReflectionEnum $enum): EnumMetadata
    {
        /** @var EnumMetadata<TArg> */
        return $this->enums[$enum->name] ??= $this->delegate->readEnum($enum);
    }
}
