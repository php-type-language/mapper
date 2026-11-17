<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Instantiator;

/**
 * Creates objects without calling the constructor, the default values of
 * the properties are set. The class is checked by
 * {@see InstantiatorFactory}.
 *
 * @template T of object
 *
 * @template-implements InstantiatorInterface<T>
 */
final class WithoutConstructorInstantiator implements InstantiatorInterface
{
    public function __construct(
        /**
         * @var \ReflectionClass<T>
         */
        private readonly \ReflectionClass $class,
    ) {}

    /**
     * The data is not used, the object is always a new one.
     *
     * @throws \ReflectionException in case of the class is an internal one
     *         requiring the constructor
     */
    public function instantiate(array $data = []): object
    {
        return $this->class->newInstanceWithoutConstructor();
    }
}
