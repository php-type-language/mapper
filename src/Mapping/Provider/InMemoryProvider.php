<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Provider;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

final class InMemoryProvider extends Provider
{
    /**
     * @var array<class-string, ClassMetadata<object>>
     */
    private array $memory = [];

    public function getClassMetadata(
        \ReflectionClass $class,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ClassMetadata {
        // @phpstan-ignore-next-line : class-string<T> key contains ClassMetadata<T> instance
        return $this->memory[$class->name]
            ??= parent::getClassMetadata($class, $types, $parser);
    }
}
