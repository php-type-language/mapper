<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserRuntimeInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryRuntimeInterface;

final class InMemoryCachedDriver extends Driver
{
    /**
     * @var array<class-string, ClassMetadata<object>>
     */
    private array $memory = [];

    public function getClassMetadata(
        \ReflectionClass $class,
        TypeRepositoryRuntimeInterface $types,
        TypeParserRuntimeInterface $parser,
    ): ClassMetadata {
        // @phpstan-ignore-next-line : class-string<T> key contains ClassMetadata<T> instance
        return $this->memory[$class->name]
            ??= parent::getClassMetadata($class, $types, $parser);
    }
}
