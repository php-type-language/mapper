<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Context;

class Context
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

    /**
     * Enables or disables object to arrays conversion.
     *
     * In case of $enabled is {@see null} a default value will be defined.
     *
     * @api
     */
    public function withObjectsAsArrays(?bool $enabled = null): static
    {
        $self = clone $this;
        $self->objectsAsArrays = $enabled;

        return $self;
    }

    /**
     * Returns current {@see $objectsAsArrays} option or default value
     * in case of option is not set.
     *
     * @api
     */
    public function isObjectsAsArrays(): bool
    {
        return $this->objectsAsArrays ?? self::OBJECTS_AS_ARRAYS_DEFAULT_VALUE;
    }

    /**
     * Enables or disables detailed types in exceptions.
     *
     * In case of $enabled is {@see null} a default value will be defined.
     *
     * @api
     */
    public function withDetailedTypes(?bool $enabled = null): static
    {
        $self = clone $this;
        $self->detailedTypes = $enabled;

        return $self;
    }

    /**
     * Returns current {@see $detailedTypes} option or default value
     * in case of option is not set.
     *
     * @api
     */
    public function isDetailedTypes(): bool
    {
        return $this->detailedTypes ?? self::DETAILED_TYPES_DEFAULT_VALUE;
    }
}
