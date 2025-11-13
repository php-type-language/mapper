<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Provider;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

final class InMemoryProvider extends Decorator
{
    /**
     * @var array<class-string, ClassMetadata<object>>
     */
    private array $memory = [];

    public function getClassMetadata(\ReflectionClass $class, BuildingContext $context): ClassMetadata
    {
        // @phpstan-ignore-next-line : class-string<T> key contains ClassMetadata<T> instance
        return $this->memory[$class->name]
            ??= parent::getClassMetadata($class, $context);
    }
}
