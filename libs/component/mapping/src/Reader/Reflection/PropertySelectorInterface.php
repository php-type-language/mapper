<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Reflection;

use TypeLang\Mapper\Mapping\Reader\ReflectionMetadataReader;

/**
 * Decides which properties of a class are mapped,
 * {@see ReflectionMetadataReader} adds them. Static and virtual properties
 * are not given to the selector.
 */
interface PropertySelectorInterface
{
    public function isSelected(\ReflectionProperty $property): bool;
}
