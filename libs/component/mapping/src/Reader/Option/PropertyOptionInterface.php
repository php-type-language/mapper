<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigSection;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;

/**
 * One option of a property read from its attributes and from its config,
 * such as the skip conditions. A wrong value is an error added to the
 * collector, the property is returned without it.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
interface PropertyOptionInterface
{
    /**
     * Returns whether the property or its hooks have attributes of the
     * option, an attribute of a property that is not mapped is an error.
     */
    public function hasPropertyAttributes(\ReflectionProperty $property): bool;

    public function readPropertyAttributes(
        \ReflectionProperty $property,
        PropertyMetadata $metadata,
        SourceContext $context,
        ErrorCollector $errors,
    ): PropertyMetadata;

    /**
     * Returns the keys of a property config the option reads.
     *
     * @return list<non-empty-string>
     */
    public function getPropertyKeys(): array;

    public function readPropertyConfig(
        ConfigSection $config,
        PropertyMetadata $metadata,
        ErrorCollector $errors,
    ): PropertyMetadata;
}
