<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

/**
 * Settings of the mapper. A setting is changed on a copy, the other
 * settings are kept.
 *
 * ```
 * $config = (new Configuration())
 *     ->withStrict(true)
 *     ->withMaxViolations(100);
 * ```
 */
final class Configuration
{
    public function __construct(
        /**
         * Whether a value must match the type exactly on denormalization,
         * without coercion. A property may override it.
         */
        public readonly bool $strict = false,
        /**
         * Whether an object is normalized to an array or to a
         * {@see \stdClass}. A class may override it.
         */
        public readonly bool $normalizeAsArray = true,
        /**
         * Number of violations a denormalization collects before it fails,
         * the first one fails it by default
         *
         * @var int<1, max>
         */
        public readonly int $maxViolations = 1,
        /**
         * Whether a field of the data that no property of a class and no
         * field of a sealed shape takes is a violation on denormalization,
         * such fields are dropped by default
         */
        public readonly bool $rejectExtraFields = false,
        /**
         * Number of levels of the classes described by their fields in the
         * expected types of the violations, `0` describes a class as
         * `object`
         *
         * @var int<0, max>
         */
        public readonly int $expectedTypeDepth = 1,
        /**
         * Number of fields a type is described by in the expected types of
         * the violations, the others are shown as `...`, {@see null} shows
         * all of them
         *
         * @var int<1, max>|null
         */
        public readonly ?int $expectedTypeMaxFields = null,
        /**
         * Number of levels of nested objects a normalization goes through
         * before it fails, an object and an array of a `mixed` value are a
         * level each. Stops the normalization of a cyclic graph.
         *
         * @var int<1, max>
         */
        public readonly int $maxDepth = 512,
    ) {}

    public function withStrict(bool $strict): self
    {
        return $this->with(strict: $strict);
    }

    public function withNormalizeAsArray(bool $normalizeAsArray): self
    {
        return $this->with(normalizeAsArray: $normalizeAsArray);
    }

    /**
     * @param int<1, max> $maxViolations
     */
    public function withMaxViolations(int $maxViolations): self
    {
        return $this->with(maxViolations: $maxViolations);
    }

    public function withRejectExtraFields(bool $rejectExtraFields): self
    {
        return $this->with(rejectExtraFields: $rejectExtraFields);
    }

    /**
     * @param int<0, max> $expectedTypeDepth
     */
    public function withExpectedTypeDepth(int $expectedTypeDepth): self
    {
        return $this->with(expectedTypeDepth: $expectedTypeDepth);
    }

    /**
     * @param int<1, max>|null $expectedTypeMaxFields
     */
    public function withExpectedTypeMaxFields(?int $expectedTypeMaxFields): self
    {
        return $this->with(expectedTypeMaxFields: $expectedTypeMaxFields);
    }

    /**
     * @param int<1, max> $maxDepth
     */
    public function withMaxDepth(int $maxDepth): self
    {
        return $this->with(maxDepth: $maxDepth);
    }

    /**
     * Returns the copy with the given settings replaced, by constructor
     * argument names.
     */
    private function with(mixed ...$changes): self
    {
        // @phpstan-ignore-next-line properties are the constructor arguments
        return new self(...$changes + \get_object_vars($this));
    }
}
