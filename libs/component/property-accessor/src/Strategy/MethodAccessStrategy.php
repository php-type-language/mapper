<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Strategy;

use TypeLang\Mapper\Mapping\Metadata\Access\MethodAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\FieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\InputFieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\OutputFieldMetadata;
use TypeLang\Mapper\PropertyAccessor\Exception\MethodNotCallableException;
use TypeLang\Mapper\PropertyAccessor\PropertyReader\MethodPropertyReader;
use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyWriter\MethodPropertyWriter;
use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;

/**
 * Takes {@see MethodAccessMetadata}: calls a public instance method, a
 * getter without required parameters, a setter taking one value.
 */
final class MethodAccessStrategy implements AccessStrategyInterface
{
    /**
     * @throws MethodNotCallableException in case of the class has no public
     *         instance method of the access or the method requires arguments
     */
    public function tryCreateReader(\ReflectionClass $class, OutputFieldMetadata $field): ?PropertyReaderInterface
    {
        $access = $field->access;

        if (!$access instanceof MethodAccessMetadata) {
            return null;
        }

        $method = self::getMethod($class, $field, $access);

        if ($method->getNumberOfRequiredParameters() > 0) {
            throw MethodNotCallableException::becauseGetterRequiresArguments(
                $class->name,
                $field->name,
                $method->name,
            );
        }

        return new MethodPropertyReader($access->method);
    }

    /**
     * @throws MethodNotCallableException in case of the class has no public
     *         instance method of the access or the method does not take one
     *         value
     */
    public function tryCreateWriter(\ReflectionClass $class, InputFieldMetadata $field): ?PropertyWriterInterface
    {
        $access = $field->access;

        if (!$access instanceof MethodAccessMetadata) {
            return null;
        }

        $method = self::getMethod($class, $field, $access);

        if ($method->getNumberOfParameters() === 0
            || $method->getNumberOfRequiredParameters() > 1
            || $method->getParameters()[0]->isVariadic()) {
            throw MethodNotCallableException::becauseSetterDoesNotTakeOneValue(
                $class->name,
                $field->name,
                $method->name,
            );
        }

        return new MethodPropertyWriter($access->method);
    }

    /**
     * @param \ReflectionClass<object> $class
     * @throws MethodNotCallableException in case of the class has no method
     *         of the access or it is not a public instance one
     */
    private static function getMethod(
        \ReflectionClass $class,
        FieldMetadata $field,
        MethodAccessMetadata $access,
    ): \ReflectionMethod {
        if (!$class->hasMethod($access->method)) {
            throw MethodNotCallableException::becauseMethodDoesNotExist($class->name, $field->name, $access->method);
        }

        $method = $class->getMethod($access->method);

        if (!$method->isPublic()) {
            throw MethodNotCallableException::becauseMethodIsNotPublic($class->name, $field->name, $method->name);
        }

        if ($method->isStatic()) {
            throw MethodNotCallableException::becauseMethodIsStatic($class->name, $field->name, $method->name);
        }

        return $method;
    }
}
