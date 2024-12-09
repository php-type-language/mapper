<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ClassType\ClassInstantiator;

use TypeLang\Mapper\Exception\Mapping\NonInstantiatableObjectException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Context;

final class ReflectionClassInstantiator implements ClassInstantiatorInterface
{
    public function instantiate(ClassMetadata $class, Context $context): object
    {
        $reflection = new \ReflectionClass($class->getName());

        if (!$reflection->isInstantiable()) {
            throw NonInstantiatableObjectException::createFromContext(
                expected: $class->getTypeStatement($context),
                value: $reflection,
                context: $context,
            );
        }

        return $reflection->newInstanceWithoutConstructor();
    }
}
