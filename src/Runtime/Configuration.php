<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime;

use Psr\Log\LoggerInterface;
use TypeLang\Mapper\Runtime\Tracing\TracerInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * Default value for {@see $objectsAsArrays} option.
     */
    public const OBJECTS_AS_ARRAYS_DEFAULT_VALUE = true;

    /**
     * Default value for {@see $detailedTypes} option.
     */
    public const DETAILED_TYPES_DEFAULT_VALUE = true;

    /**
     * Default value for {@see $strictTypes} option.
     */
    public const STRICT_TYPES_DEFAULT_VALUE = true;

    public function __construct(
        /**
         * If this option contains {@see true}, then objects are converted to
         * associative arrays, otherwise anonymous {@see object} will be
         * returned.
         */
        private ?bool $objectsAsArrays = null,
        /**
         * If this option contains {@see true}, then all composite types will
         * be displayed along with detailed fields/values.
         */
        private ?bool $detailedTypes = null,
        /**
         * If this option contains {@see true}, then strict types will
         * be enabled.
         */
        private ?bool $strictTypes = null,
        /**
         * If this option contains {@see LoggerInterface}, then logger
         * will be enabled. Otherwise logger will be disabled in case of
         * argument contain {@see null}.
         */
        private ?LoggerInterface $logger = null,
        /**
         * If this option contains {@see TracerInterface}, then an application
         * tracing will be enabled using given tracer. Otherwise an application
         * tracing will be disabled in case of argument contain {@see null}.
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
        $self->objectsAsArrays = $enabled;

        return $self;
    }

    public function isObjectsAsArrays(): bool
    {
        return $this->objectsAsArrays ?? self::OBJECTS_AS_ARRAYS_DEFAULT_VALUE;
    }

    /**
     * Returns {@see true} in case option is user-defined.
     *
     * @api
     */
    public function isObjectsAsArraysOptionDefined(): bool
    {
        return $this->objectsAsArrays !== null;
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
        $self->detailedTypes = $enabled;

        return $self;
    }

    public function isDetailedTypes(): bool
    {
        return $this->detailedTypes ?? self::DETAILED_TYPES_DEFAULT_VALUE;
    }

    /**
     * Returns {@see true} in case option is user-defined.
     *
     * @api
     */
    public function isDetailedTypesOptionDefined(): bool
    {
        return $this->detailedTypes !== null;
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
        $self->strictTypes = $enabled;

        return $self;
    }

    public function isStrictTypesEnabled(): bool
    {
        return $this->strictTypes ?? self::STRICT_TYPES_DEFAULT_VALUE;
    }

    /**
     * Returns {@see true} in case option is user-defined.
     *
     * @api
     */
    public function isStrictTypesOptionDefined(): bool
    {
        return $this->strictTypes !== null;
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
