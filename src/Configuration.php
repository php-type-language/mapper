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
    public const DEFAULT_OBJECT_AS_ARRAY_OPTION = true;

    /**
     * Default value for {@see $strictTypes} option.
     */
    public const DEFAULT_STRICT_TYPES_OPTION = true;

    /**
     * Default value for {@see $typeSpecifiers} option.
     */
    public const DEFAULT_TYPE_SPECIFIERS_OPTION = true;

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
         * If this option contains {@see true}, then type specifiers will
         * be applied.
         */
        private readonly ?bool $typeSpecifiers = null,
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
            objectAsArray: $enabled,
            strictTypes: $this->strictTypes,
            typeSpecifiers: $this->typeSpecifiers,
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
        return $this->objectAsArray
            ?? self::DEFAULT_OBJECT_AS_ARRAY_OPTION;
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
            typeSpecifiers: $this->typeSpecifiers,
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
        return $this->strictTypes
            ?? self::DEFAULT_STRICT_TYPES_OPTION;
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
     * Enables or disables type specifiers while casting.
     *
     * In case of $enabled is {@see null} a default value will be defined.
     *
     * @api
     */
    public function withTypeSpecifiers(?bool $enabled = null): self
    {
        return new self(
            objectAsArray: $this->objectAsArray,
            strictTypes: $this->strictTypes,
            typeSpecifiers: $enabled,
            logger: $this->logger,
            tracer: $this->tracer,
        );
    }

    /**
     * In case of method returns {@see true}, all types will be checked
     * for additional assertions.
     */
    public function isTypeSpecifiersEnabled(): bool
    {
        return $this->typeSpecifiers
            ?? self::DEFAULT_TYPE_SPECIFIERS_OPTION;
    }

    /**
     * Returns {@see true} in case option is user-defined.
     *
     * @api
     */
    public function isTypeSpecifierOptionDefined(): bool
    {
        return $this->typeSpecifiers !== null;
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
            objectAsArray: $this->objectAsArray,
            strictTypes: $this->strictTypes,
            typeSpecifiers: $this->typeSpecifiers,
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
            objectAsArray: $this->objectAsArray,
            strictTypes: $this->strictTypes,
            typeSpecifiers: $this->typeSpecifiers,
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
