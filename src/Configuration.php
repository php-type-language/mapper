<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use Psr\Log\LoggerInterface;
use TypeLang\Mapper\Tracing\TracerInterface;

final class Configuration
{
    /**
     * Default value for {@see $isObjectAsArray} option.
     */
    public const OBJECT_AS_ARRAY_DEFAULT_VALUE = true;

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
        private readonly ?bool $isObjectAsArray = null,
        /**
         * If this option contains {@see true}, then strict types will
         * be enabled.
         */
        private readonly ?bool $isStrictTypes = null,
        /**
         * If this option contains {@see LoggerInterface}, then logger
         * will be enabled.
         *
         * Logger will be disabled in case of argument contain {@see null}.
         */
        private readonly ?LoggerInterface $logger = null,
        /**
         * If this option contains {@see TracerInterface}, then an application
         * tracing will be enabled using given tracer.
         *
         * An application tracing will be disabled in case of argument
         * contain {@see null}.
         */
        private readonly ?TracerInterface $tracer = null,
    ) {}

    /**
     * Enables or disables object to arrays conversion.
     *
     * In case of $enabled is {@see null} a default value will be defined.
     *
     * @api
     */
    public function withObjectAsArray(?bool $enabled = null): self
    {
        return new self(
            isObjectAsArray: $enabled,
            isStrictTypes: $this->isStrictTypes,
            logger: $this->logger,
            tracer: $this->tracer,
        );
    }

    /**
     * Specifies the default normalization settings for the object.
     *
     * In case of the method returns {@see true}, the object will be converted
     * to an associative array (hash map) unless otherwise specified.
     */
    public function isObjectAsArray(): bool
    {
        return $this->isObjectAsArray
            ?? self::OBJECT_AS_ARRAY_DEFAULT_VALUE;
    }

    /**
     * Returns {@see true} in case option is user-defined.
     *
     * @api
     */
    public function isObjectAsArrayOptionDefined(): bool
    {
        return $this->isObjectAsArray !== null;
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
        return new self(
            isObjectAsArray: $this->isObjectAsArray,
            isStrictTypes: $enabled,
            logger: $this->logger,
            tracer: $this->tracer,
        );
    }

    /**
     * In case of method returns {@see true}, all types will be checked
     * for compliance.
     *
     * Otherwise, the value will attempt to be converted to the
     * required type if possible.
     */
    public function isStrictTypesEnabled(): bool
    {
        return $this->isStrictTypes
            ?? self::STRICT_TYPES_DEFAULT_VALUE;
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
        return new self(
            isObjectAsArray: $this->isObjectAsArray,
            isStrictTypes: $this->isStrictTypes,
            logger: $logger,
            tracer: $this->tracer,
        );
    }

    /**
     * If this method returns {@see LoggerInterface}, then the given logger
     * will be enabled. Otherwise logger should be disabled.
     */
    public function findLogger(): ?LoggerInterface
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
        return new self(
            isObjectAsArray: $this->isObjectAsArray,
            isStrictTypes: $this->isStrictTypes,
            logger: $this->logger,
            tracer: $tracer,
        );
    }

    /**
     * If this method returns {@see TracerInterface}, then the application
     * tracing will be enabled. Otherwise tracing should be disabled.
     */
    public function findTracer(): ?TracerInterface
    {
        return $this->tracer;
    }
}
