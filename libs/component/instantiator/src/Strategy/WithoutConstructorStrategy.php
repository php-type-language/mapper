<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Instantiator\Strategy;

use TypeLang\Mapper\Instantiator\Exception\ClassNotInstantiableException;
use TypeLang\Mapper\Instantiator\InstantiatorInterface;
use TypeLang\Mapper\Instantiator\WithoutConstructorInstantiator;
use TypeLang\Mapper\Mapping\Metadata\Access\HookAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\SlotAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

/**
 * Creates the objects of a class without calling the constructor. An
 * abstract class, an interface, a trait and an enum are not taken.
 *
 * The default value of a promoted property is written on creating the
 * object, as PHP writes the default value of another property, unless a
 * field of the denormalization writes the property.
 */
final class WithoutConstructorStrategy implements InstantiationStrategyInterface
{
    /**
     * @throws ClassNotInstantiableException in case of the class is an
     *         internal one requiring the constructor
     */
    public function tryCreateInstantiator(\ReflectionClass $class, ClassMetadata $metadata): ?InstantiatorInterface
    {
        if ($class->isAbstract() || $class->isInterface() || $class->isTrait() || $class->isEnum()) {
            return null;
        }

        $instantiator = new WithoutConstructorInstantiator($class, self::getDefaults($class, $metadata));

        // Probe of an internal final class, such a class may require the
        // constructor.
        if ($class->isInternal() && $class->isFinal()) {
            try {
                $instantiator->instantiate([]);
            } catch (\ReflectionException $e) {
                throw ClassNotInstantiableException::becauseClassRequiresConstructor($class->name, $e);
            }
        }

        return $instantiator;
    }

    /**
     * Returns the promoted properties with default values the fields of the
     * denormalization do not write: of the constructors of the class and of
     * its parents.
     *
     * @param \ReflectionClass<object> $class
     * @param ClassMetadata<object> $metadata
     * @return list<array{\ReflectionProperty, \ReflectionParameter}>
     * @throws \ReflectionException
     */
    private static function getDefaults(\ReflectionClass $class, ClassMetadata $metadata): array
    {
        $result = [];

        for ($current = $class; $current !== false; $current = $current->getParentClass()) {
            $constructor = $current->getConstructor();

            if ($constructor === null || $constructor->class !== $current->name) {
                continue;
            }

            foreach ($constructor->getParameters() as $parameter) {
                if (!$parameter->isPromoted() || !$parameter->isDefaultValueAvailable()) {
                    continue;
                }

                $property = new \ReflectionProperty($current->name, $parameter->name);

                if (!self::isWritten($property, $metadata)) {
                    $result[] = [$property, $parameter];
                }
            }
        }

        return $result;
    }

    /**
     * @param ClassMetadata<object> $metadata
     */
    private static function isWritten(\ReflectionProperty $property, ClassMetadata $metadata): bool
    {
        foreach ($metadata->input->fields as $field) {
            $access = $field->access;

            if (($access instanceof SlotAccessMetadata || $access instanceof HookAccessMetadata)
                && $access->property === $property->name
                && ($access->class === null || $access->class === $property->class)) {
                return true;
            }
        }

        return false;
    }
}
