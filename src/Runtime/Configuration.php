<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime;

use Psr\Log\LoggerInterface;
use TypeLang\Mapper\Runtime\Tracing\TracerInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * Default value for {@see $isObjectsAsArrays} option.
     */
    public const OBJECTS_AS_ARRAYS_DEFAULT_VALUE = true;

    /**
     * Default value for {@see $isDetailedTypes} option.
     */
    public const DETAILED_TYPES_DEFAULT_VALUE = true;

    /**
     * Default value for {@see $isStrictTypes} option.
     */
    public const STRICT_TYPES_DEFAULT_VALUE = true;

    public function __construct(
        /**
         * If this option contains {@see true}, then objects are converted to
         * associative arrays, otherwise anonymous {@see object} will be
         * returned.
         */
        private ?bool $isObjectsAsArrays = null,
        /**
         * If this option contains {@see true}, then all composite types will
         * be displayed along with detailed fields/values.
         */
        private ?bool $isDetailedTypes = null,
        /**
         * If this option contains {@see true}, then strict types will
         * be enabled.
         */
        private ?bool $isStrictTypes = null,
        /**
         * If this option contains {@see LoggerInterface}, then logger
         * will be enabled.
         *
         * Logger will be disabled in case of argument contain {@see null}.
         */
        private ?LoggerInterface $logger = null,
        /**
         * If this option contains {@see TracerInterface}, then an application
         * tracing will be enabled using given tracer.
         *
         * An application tracing will be disabled in case of argument
         * contain {@see null}.
         */
        private ?TracerInterface $tracer = null,
    ) {}

    /**
     * Enables or disables object to arrays conversion.
     *
     * In case of $enabled is {@see null} a default value will be defined.
     *
     * @api
     */
    public function withObjectsAsArrays(?bool $enabled = null): self
    {
        $self = clone $this;
        $self->isObjectsAsArrays = $enabled;

        return $self;
    }

    public function isObjectsAsArrays(): bool
    {
        return $this->isObjectsAsArrays ?? self::OBJECTS_AS_ARRAYS_DEFAULT_VALUE;
    }

    /**
     * Returns {@see true} in case option is user-defined.
     *
     * @api
     */
    public function isObjectsAsArraysOptionDefined(): bool
    {
        return $this->isObjectsAsArrays !== null;
    }

    /**
     * Enables or disables detailed types in exceptions.
     *
     * In case of $enabled is {@see null} a default value will be defined.
     *
     * @api
     */
    public function withDetailedTypes(?bool $enabled = null): self
    {
        $self = clone $this;
        $self->isDetailedTypes = $enabled;

        return $self;
    }

    public function isDetailedTypes(): bool
    {
        return $this->isDetailedTypes ?? self::DETAILED_TYPES_DEFAULT_VALUE;
    }

    /**
     * Returns {@see true} in case option is user-defined.
     *
     * @api
     */
    public function isDetailedTypesOptionDefined(): bool
    {
        return $this->isDetailedTypes !== null;
    }

    /**
     * Enables or disables strict types in casting.
     *
     * In case of $enabled is {@see null} a default value will be defined.
     *
     * @api
     */
    public function withStrictTypes(?bool $enabled = null): self
    {
        $self = clone $this;
        $self->isStrictTypes = $enabled;

        return $self;
    }

    public function isStrictTypesEnabled(): bool
    {
        return $this->isStrictTypes ?? self::STRICT_TYPES_DEFAULT_VALUE;
    }

    /**
     * Returns {@see true} in case option is user-defined.
     *
     * @api
     */
    public function isStrictTypesOptionDefined(): bool
    {
        return $this->isStrictTypes !== null;
    }

    /**
     * Enables logging using passed instance in case of {@see LoggerInterface}
     * instance is present or disables it in case of logger is {@see null}.
     *
     * @api
     */
    public function withLogger(?LoggerInterface $logger = null): self
    {
        $self = clone $this;
        $self->logger = $logger;

        return $self;
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Enables application tracing using passed instance in case of
     * {@see TracerInterface} instance is present or disables it in case of
     * tracer is {@see null}.
     *
     * @api
     */
    public function withTracer(?TracerInterface $tracer = null): self
    {
        $self = clone $this;
        $self->tracer = $tracer;

        return $self;
    }

    public function getTracer(): ?TracerInterface
    {
        return $this->tracer;
    }
}
