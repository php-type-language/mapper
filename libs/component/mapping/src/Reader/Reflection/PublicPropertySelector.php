<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Reflection;

/**
 * Selects public properties, including inherited ones.
 */
final class PublicPropertySelector implements PropertySelectorInterface
{
    public function isSelected(\ReflectionProperty $property): bool
    {
        return $property->isPublic();
    }
}
