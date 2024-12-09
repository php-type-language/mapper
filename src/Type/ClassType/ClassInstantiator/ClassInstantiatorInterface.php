<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ClassType\ClassInstantiator;

use TypeLang\Mapper\Exception\Mapping\NonInstantiatableObjectException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Context;

interface ClassInstantiatorInterface
{
    /**
     * @template T of object
     *
     * @param ClassMetadata<T> $class
     *
     * @return T
     * @throws NonInstantiatableObjectException occurs in case of object is not instantiatable
     * @throws \Throwable occurs for some reason when creating an object
     */
    public function instantiate(ClassMetadata $class, Context $context): object;
}
