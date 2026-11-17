<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Naming\NamingStrategyInterface;

/**
 * Names the given properties without an explicit external name by the
 * naming strategy of the class or, in case of the class has none, by the
 * given one. Adds no properties.
 */
final class NamingStrategyMetadataReader implements MetadataReaderInterface
{
    public function __construct(
        /**
         * Strategy of the classes without their own, the names are kept in
         * case of {@see null}
         */
        private readonly ?NamingStrategyInterface $strategy = null,
    ) {}

    public function read(\ReflectionClass $class, ?ClassMetadata $metadata = null): ClassMetadata
    {
        $metadata ??= new ClassMetadata($class->getName());
        $strategy = $metadata->namingStrategy ?? $this->strategy;

        if ($strategy === null) {
            return $metadata;
        }

        foreach ($metadata->properties as $property) {
            if (!$property->isExternalNameExplicit) {
                $metadata = $metadata->withProperty(
                    $property->withExternalName($strategy->rename($property->name)),
                );
            }
        }

        return $metadata;
    }
}
