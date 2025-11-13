<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Provider;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

final class NullProvider implements ProviderInterface
{
    public function getClassMetadata(\ReflectionClass $class, BuildingContext $context): ClassMetadata
    {
        return new ClassMetadata($class->name);
    }
}
