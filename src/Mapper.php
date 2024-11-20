<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Runtime\Context\RootContext;
use TypeLang\Mapper\Runtime\Parser\InMemoryTypeParser;
use TypeLang\Mapper\Runtime\Parser\LoggableTypeParser;
use TypeLang\Mapper\Runtime\Parser\TraceableTypeParser;
use TypeLang\Mapper\Runtime\Parser\TypeParser;
use TypeLang\Mapper\Runtime\Parser\TypeParserFacade;
use TypeLang\Mapper\Runtime\Parser\TypeParserFacadeInterface;
use TypeLang\Mapper\Runtime\Repository\InMemoryTypeRepository;
use TypeLang\Mapper\Runtime\Repository\LoggableTypeRepository;
use TypeLang\Mapper\Runtime\Repository\TraceableTypeRepository;
use TypeLang\Mapper\Runtime\Repository\TypeRepository;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryFacade;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryFacadeInterface;
use TypeLang\Mapper\Type\TypeInterface;

final class Mapper implements NormalizerInterface, DenormalizerInterface
{
    private readonly TypeRepositoryFacadeInterface $types;

    private readonly TypeParserFacadeInterface $parser;

    public function __construct(
        private readonly PlatformInterface $platform = new StandardPlatform(),
        private readonly Configuration $config = new Configuration(),
    ) {
        $this->parser = $this->createTypeParser($platform);
        $this->types = $this->createTypeRepository($platform);
    }

    private function createTypeParser(PlatformInterface $platform): TypeParserFacadeInterface
    {
        $runtime = TypeParser::createFromPlatform($platform);

        if (($tracer = $this->config->getTracer()) !== null) {
            $runtime = new TraceableTypeParser($tracer, $runtime);
        }

        if (($logger = $this->config->getLogger()) !== null) {
            $runtime = new LoggableTypeParser($logger, $runtime);
        }

        return new TypeParserFacade(new InMemoryTypeParser(
            delegate: $runtime,
        ));
    }

    private function createTypeRepository(PlatformInterface $platform): TypeRepositoryFacadeInterface
    {
        $runtime = new InMemoryTypeRepository(
            delegate: TypeRepository::createFromPlatform(
                platform: $platform,
                parser: $this->parser,
            ),
        );

        if (($tracer = $this->config->getTracer()) !== null) {
            $runtime = new TraceableTypeRepository($tracer, $runtime);
        }

        if (($logger = $this->config->getLogger()) !== null) {
            $runtime = new LoggableTypeRepository($logger, $runtime);
        }

        return new TypeRepositoryFacade(
            parser: $this->parser,
            runtime: $runtime,
        );
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
     * Returns current types registry.
     *
     * @api
     */
    public function getTypes(): TypeRepositoryFacadeInterface
    {
        return $this->types;
    }

    /**
     * Returns current types parser.
     *
     * @api
     */
    public function getParser(): TypeParserFacadeInterface
    {
        return $this->parser;
    }

    public function normalize(mixed $value, #[Language('PHP')] ?string $type = null): mixed
    {
        $instance = $type === null
            ? $this->types->getTypeByValue($value)
            : $this->types->getTypeByDefinition($type);

        return $instance->cast($value, RootContext::forNormalization(
            value: $value,
            config: $this->config,
            parser: $this->parser,
            types: $this->types,
        ));
    }

    public function isNormalizable(mixed $value, #[Language('PHP')] ?string $type = null): bool
    {
        $instance = $type === null
            ? $this->types->getTypeByValue($value)
            : $this->types->getTypeByDefinition($type);

        return $instance->match($value, RootContext::forNormalization(
            value: $value,
            config: $this->config,
            parser: $this->parser,
            types: $this->types,
        ));
    }

    public function denormalize(mixed $value, #[Language('PHP')] string $type): mixed
    {
        $instance = $this->types->getTypeByDefinition($type);

        return $instance->cast($value, RootContext::forDenormalization(
            value: $value,
            config: $this->config,
            parser: $this->parser,
            types: $this->types,
        ));
    }

    public function isDenormalizable(mixed $value, #[Language('PHP')] string $type): bool
    {
        $instance = $this->types->getTypeByDefinition($type);

        return $instance->match($value, RootContext::forDenormalization(
            value: $value,
            config: $this->config,
            parser: $this->parser,
            types: $this->types,
        ));
    }

    /**
     * Returns type for mapping by signature.
     *
     * @api
     * @param non-empty-string $type
     * @throws TypeNotFoundException in case of type not found
     * @throws \Throwable in case of internal error occurs
     */
    public function getType(#[Language('PHP')] string $type): TypeInterface
    {
        return $this->types->getTypeByDefinition($type);
    }

    /**
     * Returns type for mapping by value.
     *
     * @api
     * @throws TypeNotFoundException in case of type not found
     * @throws \Throwable in case of internal error occurs
     */
    public function getTypeByValue(mixed $value): TypeInterface
    {
        return $this->types->getTypeByValue($value);
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

        $this->types->getTypeByDefinition($class);
    }
}
