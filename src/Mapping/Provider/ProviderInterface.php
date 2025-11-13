<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Provider;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

interface ProviderInterface
{
    /**
     * @template T of object
     *
     * @param \ReflectionClass<T> $class
     *
     * @return ClassMetadata<T>
     */
    public function getClassMetadata(\ReflectionClass $class, BuildingContext $context): ClassMetadata;
}
