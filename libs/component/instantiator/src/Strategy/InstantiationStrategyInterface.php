<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Instantiator\Strategy;

use TypeLang\Mapper\Instantiator\Exception\InstantiatorExceptionInterface;
use TypeLang\Mapper\Instantiator\InstantiatorInterface;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

/**
 * One way of creating the objects of a class, such as taking them from a
 * DI container or a database, chosen by the declaration of the class and
 * its metadata, such as by an attribute of the class.
 *
 * ```
 * public function tryCreateInstantiator(\ReflectionClass $class, ClassMetadata $metadata): ?InstantiatorInterface
 * {
 *     if ($class->getAttributes(Entity::class) === []) {
 *         return null;
 *     }
 *
 *     return new RepositoryInstantiator($this->entities, $class->name);
 * }
 * ```
 */
interface InstantiationStrategyInterface
{
    /**
     * Returns {@see null} in case of the strategy does not create the
     * objects of the class.
     *
     * @template TArgObject of object
     * @param \ReflectionClass<TArgObject> $class
     * @param ClassMetadata<TArgObject> $metadata
     * @return InstantiatorInterface<TArgObject>|null
     * @throws InstantiatorExceptionInterface in case of the strategy takes
     *         the class, but its objects cannot be created
     */
    public function tryCreateInstantiator(\ReflectionClass $class, ClassMetadata $metadata): ?InstantiatorInterface;
}
