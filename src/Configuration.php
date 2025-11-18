<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use Psr\Log\LoggerInterface;
use TypeLang\Mapper\Kernel\Extractor\Factory\DefaultTypeExtractorFactory;
use TypeLang\Mapper\Kernel\Extractor\Factory\TypeExtractorFactoryInterface;
use TypeLang\Mapper\Kernel\Parser\Factory\DefaultTypeParserFactory;
use TypeLang\Mapper\Kernel\Parser\Factory\TypeParserFactoryInterface;
use TypeLang\Mapper\Kernel\Repository\Factory\DefaultTypeRepositoryFactory;
use TypeLang\Mapper\Kernel\Repository\Factory\TypeRepositoryFactoryInterface;
use TypeLang\Mapper\Kernel\Tracing\TracerInterface;

class Configuration
{
    /**
     * Default value for {@see $objectAsArray} option.
     */
    public const OBJECT_AS_ARRAY_DEFAULT_VALUE = true;

    /**
     * Default value for {@see $strictTypes} option.
     */
    public const STRICT_TYPES_DEFAULT_VALUE = false;

    public function __construct(
        /**
         * If this option contains {@see true}, then objects are converted to
         * associative arrays, otherwise anonymous {@see object} will be
         * returned.
         *
         * @phpstan-readonly-allow-private-mutation
         */
        private ?bool $objectAsArray = null,
        /**
         * If this option contains {@see true}, then strict types will
         * be enabled.
         *
         * @phpstan-readonly-allow-private-mutation
         */
        private ?bool $strictTypes = null,
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
        /**
         * A type extractor is a class responsible for inferring
         * a type from a value.
         *
         * Define this field explicitly to set a specific type extractor
         *
         * @link https://typelang.dev/type-extractors.html
         */
        private readonly TypeExtractorFactoryInterface $typeExtractorFactory = new DefaultTypeExtractorFactory(),
        /**
         * A type parser is a class responsible for parse type definitions
         * to full-fledged AST nodes
         *
         * Define this field explicitly to set a specific type parser
         */
        private readonly TypeParserFactoryInterface $typeParserFactory = new DefaultTypeParserFactory(),
        /**
         * A type repository is a class responsible to return specific
         * type instances
         *
         * Define this field explicitly to set a specific type repository
         */
        private readonly TypeRepositoryFactoryInterface $typeRepositoryFactory = new DefaultTypeRepositoryFactory(),
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
        $self = clone $this;
        $self->objectAsArray = $enabled;

        return $self;
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
        $self = clone $this;
        $self->strictTypes = $enabled;

        return $self;
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
     * Gets mapper type repository factory
     */
    public function getTypeRepositoryFactory(): TypeRepositoryFactoryInterface
    {
        return $this->typeRepositoryFactory;
    }

    /**
     * Gets mapper type parser factory
     */
    public function getTypeParserFactory(): TypeParserFactoryInterface
    {
        return $this->typeParserFactory;
    }

    /**
     * Gets mapper type extractor factory
     */
    public function getTypeExtractorFactory(): TypeExtractorFactoryInterface
    {
        return $this->typeExtractorFactory;
    }
}
