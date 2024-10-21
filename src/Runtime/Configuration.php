<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime;

final class Configuration implements ConfigurationInterface, EvolvableConfigurationInterface
{
    /**
     * Default value for {@see $objectsAsArrays} option.
     */
    public const OBJECTS_AS_ARRAYS_DEFAULT_VALUE = true;

    /**
     * Default value for {@see $detailedTypes} option.
     */
    public const DETAILED_TYPES_DEFAULT_VALUE = true;

    public function __construct(
        /**
         * If this option contains {@see true}, then objects are converted to
         * associative arrays, otherwise anonymous {@see object} will be
         * returned.
         */
        protected ?bool $objectsAsArrays = null,
        /**
         * If this option contains {@see true}, then all composite types will
         * be displayed along with detailed fields/values.
         */
        protected ?bool $detailedTypes = null,
    ) {}

    public function withObjectsAsArrays(?bool $enabled = null): self
    {
        $self = clone $this;
        $self->objectsAsArrays = $enabled;

        return $self;
    }

    public function isObjectsAsArrays(): bool
    {
        return $this->objectsAsArrays ?? self::OBJECTS_AS_ARRAYS_DEFAULT_VALUE;
    }

    public function withDetailedTypes(?bool $enabled = null): self
    {
        $self = clone $this;
        $self->detailedTypes = $enabled;

        return $self;
    }

    public function isDetailedTypes(): bool
    {
        return $this->detailedTypes ?? self::DETAILED_TYPES_DEFAULT_VALUE;
    }
}
