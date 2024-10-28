<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Mapping\RuntimeExceptionInterface;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Runtime\Context\RootContext;
use TypeLang\Mapper\Runtime\Parser\InMemoryTypeParserRuntime;
use TypeLang\Mapper\Runtime\Parser\LoggableTypeParserRuntime;
use TypeLang\Mapper\Runtime\Parser\TraceableTypeParserRuntime;
use TypeLang\Mapper\Runtime\Parser\TypeParser;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserRuntime;
use TypeLang\Mapper\Runtime\Repository\InMemoryTypeRepositoryRuntime;
use TypeLang\Mapper\Runtime\Repository\LoggableTypeRepositoryRuntime;
use TypeLang\Mapper\Runtime\Repository\TraceableTypeRepositoryRuntime;
use TypeLang\Mapper\Runtime\Repository\TypeRepository;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryRuntime;
use TypeLang\Mapper\Type\TypeInterface;

final class Mapper implements NormalizerInterface, DenormalizerInterface
{
    private readonly TypeRepositoryInterface $types;

    private readonly TypeParserInterface $parser;

    public function __construct(
        private readonly PlatformInterface $platform = new StandardPlatform(),
        private readonly Configuration $config = new Configuration(),
    ) {
        $this->parser = $this->createTypeParser($platform);
        $this->types = $this->createTypeRepository($platform);
    }

    private function createTypeParser(PlatformInterface $platform): TypeParserInterface
    {
        $runtime = TypeParserRuntime::createFromPlatform($platform);

        if (($tracer = $this->config->getTracer()) !== null) {
            $runtime = new TraceableTypeParserRuntime($tracer, $runtime);
        }

        if (($logger = $this->config->getLogger()) !== null) {
            $runtime = new LoggableTypeParserRuntime($logger, $runtime);
        }

        return new TypeParser(new InMemoryTypeParserRuntime(
            delegate: $runtime,
        ));
    }

    private function createTypeRepository(PlatformInterface $platform): TypeRepositoryInterface
    {
        $runtime = TypeRepositoryRuntime::createFromPlatform($platform, $this->parser);

        if (($tracer = $this->config->getTracer()) !== null) {
            $runtime = new TraceableTypeRepositoryRuntime($tracer, $runtime);
        }

        if (($logger = $this->config->getLogger()) !== null) {
            $runtime = new LoggableTypeRepositoryRuntime($logger, $runtime);
        }

        return new TypeRepository(
            parser: $this->parser,
            runtime: new InMemoryTypeRepositoryRuntime($runtime),
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
    public function getTypes(): TypeRepositoryInterface
    {
        return $this->types;
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
     * @throws RuntimeExceptionInterface
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    public function normalize(mixed $value, #[Language('PHP')] ?string $type = null): mixed
    {
        $instance = $this->getType($value, $type);

        return $instance->cast($value, RootContext::forNormalization(
            value: $value,
            config: $this->config,
            parser: $this->parser,
            types: $this->types,
        ));
    }

    /**
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    public function isNormalizable(mixed $value, #[Language('PHP')] ?string $type = null): bool
    {
        $instance = $this->getType($value, $type);

        return $instance->match($value, RootContext::forNormalization(
            value: $value,
            config: $this->config,
            parser: $this->parser,
            types: $this->types,
        ));
    }

    /**
     * @throws RuntimeExceptionInterface
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    public function denormalize(mixed $value, #[Language('PHP')] string $type): mixed
    {
        $instance = $this->getType($value, $type);

        return $instance->cast($value, RootContext::forDenormalization(
            value: $value,
            config: $this->config,
            parser: $this->parser,
            types: $this->types,
        ));
    }

    /**
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    public function isDenormalizable(mixed $value, #[Language('PHP')] string $type): bool
    {
        $instance = $this->getType($value, $type);

        return $instance->match($value, RootContext::forDenormalization(
            value: $value,
            config: $this->config,
            parser: $this->parser,
            types: $this->types,
        ));
    }

    /**
     * @param non-empty-string|null $type
     *
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    private function getType(mixed $value, ?string $type): TypeInterface
    {
        if ($type === null) {
            return $this->types->getTypeByValue($value);
        }

        return $this->types->getTypeByDefinition($type);
    }
}
