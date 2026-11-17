<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Instantiator\Strategy;

use TypeLang\Mapper\Instantiator\Exception\ClassNotInstantiableException;
use TypeLang\Mapper\Instantiator\InstantiatorInterface;
use TypeLang\Mapper\Instantiator\WithoutConstructorInstantiator;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

/**
 * Creates the objects of a class without calling the constructor. An
 * abstract class, an interface, a trait and an enum are not taken.
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

        $instantiator = new WithoutConstructorInstantiator($class);

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
}
