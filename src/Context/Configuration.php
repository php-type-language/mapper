<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

/**
 * Settings of the conversion.
 */
final class Configuration
{
    public function __construct(
        /**
         * Whether a value must match the type exactly, without coercion
         */
        public readonly bool $isStrict = false,
        /**
         * Whether an object is normalized to an array or to a
         * {@see \stdClass}, a class may override it
         */
        public readonly bool $isObjectAsArray = true,
        /**
         * Number of violations a denormalization collects before it fails,
         * the first one fails it by default
         *
         * @var int<1, max>
         */
        public readonly int $maxViolations = 1,
        /**
         * Whether a field of the data that no property of a class and no
         * field of a sealed shape takes is a violation, such fields are
         * dropped by default
         */
        public readonly bool $shouldRejectExtraFields = false,
    ) {}

    /**
     * Returns a copy with the strictness overridden, the other settings are
     * kept.
     */
    public function withStrictTypes(bool $isStrict): self
    {
        return new self(
            isStrict: $isStrict,
            isObjectAsArray: $this->isObjectAsArray,
            maxViolations: $this->maxViolations,
            shouldRejectExtraFields: $this->shouldRejectExtraFields,
        );
    }
}
