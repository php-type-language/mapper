<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Instantiator;

/**
 * Creates objects without calling the constructor, the default values of
 * the properties are set. The default values of the given promoted
 * properties are written too, their hooks are bypassed. The class is
 * checked by {@see InstantiatorFactory}.
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
        /**
         * Promoted properties and the constructor parameters of their
         * default values
         *
         * @var list<array{\ReflectionProperty, \ReflectionParameter}>
         */
        private readonly array $defaults = [],
    ) {}

    /**
     * The data is not used, the object is always a new one.
     *
     * @throws \ReflectionException in case of the class is an internal one
     *         requiring the constructor or a default value cannot be
     *         evaluated
     */
    public function instantiate(array $data = []): object
    {
        $object = $this->class->newInstanceWithoutConstructor();

        // evaluated for each object, as the constructor does
        foreach ($this->defaults as [$property, $parameter]) {
            self::write($property, $object, $parameter->getDefaultValue());
        }

        return $object;
    }

    private static function write(\ReflectionProperty $property, object $object, mixed $value): void
    {
        // @phpstan-ignore-next-line PHP 8.4 API, phpVersion.min is 8.1
        if (\PHP_VERSION_ID >= 80400 && $property->hasHooks()) {
            // @phpstan-ignore-next-line PHP 8.4 API, phpVersion.min is 8.1
            $property->setRawValue($object, $value);

            return;
        }

        $property->setValue($object, $value);
    }
}
