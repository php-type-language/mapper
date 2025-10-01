<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver;

use TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

/**
 * @phpstan-import-type ClassConfigType from ArrayConfigDriver
 */
interface ClassConfigLoaderInterface
{
    /**
     * @template T of object
     *
     * @param ClassConfigType $config
     * @param \ReflectionClass<T> $class
     * @param ClassMetadata<T> $metadata
     */
    public function load(
        array $config,
        \ReflectionClass $class,
        ClassMetadata $metadata,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void;
}
