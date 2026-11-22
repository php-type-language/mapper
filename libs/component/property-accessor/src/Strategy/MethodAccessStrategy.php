<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Strategy;

use TypeLang\Mapper\Mapping\Metadata\AccessPoint\MethodAccessPoint;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\PropertyAccessor\Exception\MethodNotCallableException;
use TypeLang\Mapper\PropertyAccessor\PropertyReader\MethodPropertyReader;
use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyWriter\MethodPropertyWriter;
use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;

/**
 * Takes {@see MethodAccessPoint}: calls a public instance method, a getter
 * without required parameters, a setter taking one value.
 */
final class MethodAccessStrategy implements AccessStrategyInterface
{
    public function tryCreateReader(\ReflectionClass $class, PropertyMetadata $metadata): ?PropertyReaderInterface
    {
        $point = $metadata->read?->point;

        if (!$point instanceof MethodAccessPoint) {
            return null;
        }

        $method = self::getMethod($class, $metadata, $point);

        if ($method->getNumberOfRequiredParameters() > 0) {
            throw MethodNotCallableException::becauseGetterRequiresArguments(
                $class->name,
                $metadata->name,
                $method->name,
            );
        }

        return new MethodPropertyReader($point->method);
    }

    public function tryCreateWriter(\ReflectionClass $class, PropertyMetadata $metadata): ?PropertyWriterInterface
    {
        $point = $metadata->write?->point;

        if (!$point instanceof MethodAccessPoint) {
            return null;
        }

        $method = self::getMethod($class, $metadata, $point);

        if ($method->getNumberOfParameters() === 0 || $method->getNumberOfRequiredParameters() > 1) {
            throw MethodNotCallableException::becauseSetterDoesNotTakeOneValue(
                $class->name,
                $metadata->name,
                $method->name,
            );
        }

        return new MethodPropertyWriter($point->method);
    }

    /**
     * @param \ReflectionClass<object> $class
     * @throws MethodNotCallableException in case of the class has no method
     *         of the point or it is not a public instance one
     */
    private static function getMethod(
        \ReflectionClass $class,
        PropertyMetadata $metadata,
        MethodAccessPoint $point,
    ): \ReflectionMethod {
        if (!$class->hasMethod($point->method)) {
            throw MethodNotCallableException::becauseMethodDoesNotExist($class->name, $metadata->name, $point->method);
        }

        $method = $class->getMethod($point->method);

        if (!$method->isPublic()) {
            throw MethodNotCallableException::becauseMethodIsNotPublic($class->name, $metadata->name, $method->name);
        }

        if ($method->isStatic()) {
            throw MethodNotCallableException::becauseMethodIsStatic($class->name, $metadata->name, $method->name);
        }

        return $method;
    }
}
