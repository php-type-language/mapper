<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Mapping\Reference\Reader\NativeReferencesReader;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Runtime\Context\RootContext;
use TypeLang\Mapper\Runtime\Extractor\NativeTypeExtractor;
use TypeLang\Mapper\Runtime\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Runtime\Parser\InMemoryTypeParser;
use TypeLang\Mapper\Runtime\Parser\LoggableTypeParser;
use TypeLang\Mapper\Runtime\Parser\TraceableTypeParser;
use TypeLang\Mapper\Runtime\Parser\TypeLangParser;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\InMemoryTypeRepository;
use TypeLang\Mapper\Runtime\Repository\LoggableTypeRepository;
use TypeLang\Mapper\Runtime\Repository\TraceableTypeRepository;
use TypeLang\Mapper\Runtime\Repository\TypeRepository;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;

final class Mapper implements NormalizerInterface, DenormalizerInterface
{
    private readonly TypeExtractorInterface $extractor;

    private readonly TypeParserInterface $parser;

    private readonly TypeRepositoryInterface $types;

    public function __construct(
        private readonly PlatformInterface $platform = new StandardPlatform(),
        private readonly Configuration $config = new Configuration(),
    ) {
        $this->extractor = $this->createTypeExtractor();
        $this->parser = $this->createTypeParser($platform);
        $this->types = $this->createTypeRepository($platform);
    }

    private function createTypeExtractor(): TypeExtractorInterface
    {
        return new NativeTypeExtractor();
    }

    private function createTypeParser(PlatformInterface $platform): TypeParserInterface
    {
        $runtime = TypeLangParser::createFromPlatform($platform);

        if (($tracer = $this->config->getTracer()) !== null) {
            $runtime = new TraceableTypeParser($tracer, $runtime);
        }

        if (($logger = $this->config->getLogger()) !== null) {
            $runtime = new LoggableTypeParser($logger, $runtime);
        }

        return new InMemoryTypeParser($runtime);
    }

    private function createTypeRepository(PlatformInterface $platform): TypeRepositoryInterface
    {
        $runtime = new TypeRepository(
            parser: $this->parser,
            platform: $platform,
            references: new NativeReferencesReader(),
        );

        if (($tracer = $this->config->getTracer()) !== null) {
            $runtime = new TraceableTypeRepository($tracer, $runtime);
        }

        if (($logger = $this->config->getLogger()) !== null) {
            $runtime = new LoggableTypeRepository($logger, $runtime);
        }

        return new InMemoryTypeRepository($runtime);
    }

    /**
     * Returns current mapper platform.
     *
     * @api
     */
    public function getPlatform(): PlatformInterface
    {
        return $this->platform;
    }

    /**
     * Returns current types extractor.
     *
     * @api
     */
    public function getExtractor(): TypeExtractorInterface
    {
        return $this->extractor;
    }

    /**
     * Returns current types parser.
     *
     * @api
     */
    public function getParser(): TypeParserInterface
    {
        return $this->parser;
    }

    /**
     * Returns current types registry.
     *
     * @api
     */
    public function getTypes(): TypeRepositoryInterface
    {
        return $this->types;
    }

    public function normalize(mixed $value, #[Language('PHP')] ?string $type = null): mixed
    {
        $type ??= $this->extractor->getDefinitionByValue($value);

        $instance = $this->types->getTypeByStatement(
            statement: $this->parser->getStatementByDefinition($type),
        );

        return $instance->cast($value, RootContext::forNormalization(
            value: $value,
            config: $this->config,
            extractor: $this->extractor,
            parser: $this->parser,
            types: $this->types,
        ));
    }

    public function isNormalizable(mixed $value, #[Language('PHP')] ?string $type = null): bool
    {
        $type ??= $this->extractor->getDefinitionByValue($value);

        $instance = $this->types->getTypeByStatement(
            statement: $this->parser->getStatementByDefinition($type),
        );

        return $instance->match($value, RootContext::forNormalization(
            value: $value,
            config: $this->config,
            extractor: $this->extractor,
            parser: $this->parser,
            types: $this->types,
        ));
    }

    public function denormalize(mixed $value, #[Language('PHP')] string $type): mixed
    {
        $instance = $this->types->getTypeByStatement(
            statement: $this->parser->getStatementByDefinition($type),
        );

        return $instance->cast($value, RootContext::forDenormalization(
            value: $value,
            config: $this->config,
            extractor: $this->extractor,
            parser: $this->parser,
            types: $this->types,
        ));
    }

    public function isDenormalizable(mixed $value, #[Language('PHP')] string $type): bool
    {
        $instance = $this->types->getTypeByStatement(
            statement: $this->parser->getStatementByDefinition($type),
        );

        return $instance->match($value, RootContext::forDenormalization(
            value: $value,
            config: $this->config,
            extractor: $this->extractor,
            parser: $this->parser,
            types: $this->types,
        ));
    }

    /**
     * Returns type for mapping by signature.
     *
     * @api
     *
     * @param non-empty-string $type
     *
     * @throws TypeNotFoundException in case of type not found
     * @throws \Throwable in case of internal error occurs
     */
    public function getType(#[Language('PHP')] string $type): TypeInterface
    {
        return $this->types->getTypeByStatement(
            statement: $this->parser->getStatementByDefinition($type),
        );
    }

    /**
     * Returns type for mapping by value.
     *
     * @api
     *
     * @throws TypeNotFoundException in case of type not found
     * @throws \Throwable in case of internal error occurs
     */
    public function getTypeByValue(mixed $value): TypeInterface
    {
        $definition = $this->extractor->getDefinitionByValue($value);

        return $this->getType($definition);
    }

    /**
     * Warms up the cache for the selected class or object.
     *
     * Please note that the cache can only be warmed up if the
     * appropriate driver is used otherwise it doesn't give any effect.
     *
     * @api
     *
     * @param class-string|object $class
     *
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    public function warmup(string|object $class): void
    {
        if (\is_object($class)) {
            $class = $class::class;
        }

        $this->getType($class);
    }
}
