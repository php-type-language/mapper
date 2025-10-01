<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver\DocBlockDriver;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

interface ClassDocBlockLoaderInterface
{
    /**
     * @template T of object
     *
     * @param \ReflectionClass<T> $class
     * @param ClassMetadata<T> $metadata
     */
    public function load(
        \ReflectionClass $class,
        ClassMetadata $metadata,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void;
}
