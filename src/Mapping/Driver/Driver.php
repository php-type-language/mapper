<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserRuntimeInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryRuntimeInterface;

abstract class Driver implements DriverInterface
{
    public function __construct(
        private readonly DriverInterface $delegate = new NullDriver(),
    ) {}

    public function getClassMetadata(
        \ReflectionClass $class,
        TypeRepositoryRuntimeInterface $types,
        TypeParserRuntimeInterface $parser,
    ): ClassMetadata {
        return $this->delegate->getClassMetadata($class, $types, $parser);
    }
}
