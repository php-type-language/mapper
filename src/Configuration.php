<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use Psr\Log\LoggerInterface;
use TypeLang\Mapper\Tracing\TracerInterface;

final class Configuration
{
    /**
     * Default value for {@see $objectAsArray} option.
     */
    public const OBJECT_AS_ARRAY_DEFAULT_VALUE = true;

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
        private readonly ?bool $objectAsArray = null,
        /**
         * If this option contains {@see true}, then strict types will
         * be enabled.
         */
        private readonly ?bool $strictTypes = null,
        /**
         * Enable or disable type parsing logs
         */
        private readonly bool $logTypeParse = false,
        /**
         * Enable or disable type lookup logs
         */
        private readonly bool $logTypeFind = false,
        /**
         * Enable or disable type match logs
         */
        private readonly bool $logTypeMatch = true,
        /**
         * Enable or disable type cast logs
         */
        private readonly bool $logTypeCast = true,
        /**
         * If this option contains {@see LoggerInterface}, then logger
         * will be enabled.
         *
         * Logger will be disabled in case of argument contain {@see null}.
         */
        private readonly ?LoggerInterface $logger = null,
        /**
         * Enable or disable type parse tracing
         */
        private readonly bool $traceTypeParse = false,
        /**
         * Enable or disable type lookup tracing
         */
        private readonly bool $traceTypeFind = false,
        /**
         * Enable or disable type match tracing
         */
        private readonly bool $traceTypeMatch = true,
        /**
         * Enable or disable type cast tracing
         */
        private readonly bool $traceTypeCast = true,
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
            objectAsArray: $enabled,
            strictTypes: $this->strictTypes,
            logTypeParse: $this->logTypeParse,
            logTypeFind: $this->logTypeFind,
            logTypeMatch: $this->logTypeMatch,
            logTypeCast: $this->logTypeCast,
            logger: $this->logger,
            traceTypeParse: $this->traceTypeParse,
            traceTypeFind: $this->traceTypeFind,
            traceTypeMatch: $this->traceTypeMatch,
            traceTypeCast: $this->traceTypeCast,
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
        return $this->objectAsArray
            ?? self::OBJECT_AS_ARRAY_DEFAULT_VALUE;
    }

    /**
     * Returns {@see true} in case option is user-defined.
     *
     * @api
     */
    public function isObjectAsArrayOptionDefined(): bool
    {
        return $this->objectAsArray !== null;
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
            objectAsArray: $this->objectAsArray,
            strictTypes: $enabled,
            logTypeParse: $this->logTypeParse,
            logTypeFind: $this->logTypeFind,
            logTypeMatch: $this->logTypeMatch,
            logTypeCast: $this->logTypeCast,
            logger: $this->logger,
            traceTypeParse: $this->traceTypeParse,
            traceTypeFind: $this->traceTypeFind,
            traceTypeMatch: $this->traceTypeMatch,
            traceTypeCast: $this->traceTypeCast,
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
        return $this->strictTypes
            ?? self::STRICT_TYPES_DEFAULT_VALUE;
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
     * If this method returns {@see LoggerInterface}, then the given logger
     * will be enabled. Otherwise logger should be disabled.
     */
    public function findLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * If this method returns {@see TracerInterface}, then the application
     * tracing will be enabled. Otherwise tracing should be disabled.
     */
    public function findTracer(): ?TracerInterface
    {
        return $this->tracer;
    }

    /**
     * Returns {@see true} in case of parser logs should be enabled
     */
    public function shouldLogTypeParse(): bool
    {
        return $this->logTypeParse;
    }

    /**
     * Returns {@see true} in case of type find process logs should be enabled
     */
    public function shouldLogTypeFind(): bool
    {
        return $this->logTypeFind;
    }

    /**
     * Returns {@see true} in case of type match logs should be enabled
     */
    public function shouldLogTypeMatch(): bool
    {
        return $this->logTypeMatch;
    }

    /**
     * Returns {@see true} in case of type cast logs should be enabled
     */
    public function shouldLogTypeCast(): bool
    {
        return $this->logTypeCast;
    }

    /**
     * Returns {@see true} in case of parser tracing should be enabled
     */
    public function shouldTraceTypeParse(): bool
    {
        return $this->traceTypeParse;
    }

    /**
     * Returns {@see true} in case of type lookup tracing should be enabled
     */
    public function shouldTraceTypeFind(): bool
    {
        return $this->traceTypeFind;
    }

    /**
     * Returns {@see true} in case of type match tracing should be enabled
     */
    public function shouldTraceTypeMatch(): bool
    {
        return $this->traceTypeMatch;
    }

    /**
     * Returns {@see true} in case of type cast tracing should be enabled
     */
    public function shouldTraceTypeCast(): bool
    {
        return $this->traceTypeCast;
    }
}
