<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Context\Direction;
use TypeLang\Mapper\Context\DirectionInterface;
use TypeLang\Mapper\Context\RootRuntimeContext;
use TypeLang\Mapper\Exception\Definition\DefinitionException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Runtime\RuntimeException;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Type\Extractor\Factory\DefaultTypeExtractorFactory;
use TypeLang\Mapper\Type\Extractor\Factory\TypeExtractorFactoryInterface;
use TypeLang\Mapper\Type\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Type\Parser\Factory\DefaultTypeParserFactory;
use TypeLang\Mapper\Type\Parser\Factory\TypeParserFactoryInterface;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Repository\Factory\DefaultTypeRepositoryFactory;
use TypeLang\Mapper\Type\Repository\Factory\TypeRepositoryFactoryInterface;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;

final class Mapper implements
    NormalizerInterface,
    DenormalizerInterface,
    TypeExtractorInterface
{
    private readonly TypeExtractorInterface $extractor;

    private readonly TypeParserInterface $parser;

    /**
     * @var \WeakMap<DirectionInterface, TypeRepositoryInterface>
     */
    private readonly \WeakMap $repository;

    public function __construct(
        private readonly PlatformInterface $platform = new StandardPlatform(),
        private readonly Configuration $config = new Configuration(),
        private readonly TypeExtractorFactoryInterface $typeExtractorFactory = new DefaultTypeExtractorFactory(),
        private readonly TypeParserFactoryInterface $typeParserFactory = new DefaultTypeParserFactory(),
        private readonly TypeRepositoryFactoryInterface $typeRepositoryFactory = new DefaultTypeRepositoryFactory(),
    ) {
        $this->repository = new \WeakMap();
        $this->extractor = $this->createTypeExtractor();
        $this->parser = $this->createTypeParser();
    }

    private function createTypeExtractor(): TypeExtractorInterface
    {
        return $this->typeExtractorFactory->createTypeExtractor(
            config: $this->config,
            platform: $this->platform,
        );
    }

    private function createTypeParser(): TypeParserInterface
    {
        return $this->typeParserFactory->createTypeParser(
            config: $this->config,
            platform: $this->platform,
        );
    }

    private function getTypeRepository(DirectionInterface $direction): TypeRepositoryInterface
    {
        return $this->repository[$direction]
            ??= $this->typeRepositoryFactory->createTypeRepository(
                config: $this->config,
                platform: $this->platform,
                parser: $this->parser,
                direction: $direction,
            );
    }

    private function createContext(mixed $value, DirectionInterface $direction): RootRuntimeContext
    {
        return RootRuntimeContext::create(
            value: $value,
            direction: $direction,
            config: $this->config,
            extractor: $this->extractor,
            parser: $this->parser,
            types: $this->getTypeRepository($direction),
        );
    }

    public function normalize(mixed $value, #[Language('PHP')] ?string $type = null): mixed
    {
        $type ??= $this->getDefinitionByValue($value);

        return $this->map(Direction::Normalize, $value, $type);
    }

    public function isNormalizable(mixed $value, #[Language('PHP')] ?string $type = null): bool
    {
        $type ??= $this->getDefinitionByValue($value);

        return $this->canMap(Direction::Normalize, $value, $type);
    }

    public function denormalize(mixed $value, #[Language('PHP')] string $type): mixed
    {
        return $this->map(Direction::Denormalize, $value, $type);
    }

    public function isDenormalizable(mixed $value, #[Language('PHP')] string $type): bool
    {
        return $this->canMap(Direction::Denormalize, $value, $type);
    }

    /**
     * Map a specific data to another one using specific type.
     *
     * @param non-empty-string $type
     *
     * @throws RuntimeException in case of runtime mapping exception occurs
     * @throws DefinitionException in case of type building exception occurs
     * @throws \Throwable in case of any internal error occurs
     */
    public function map(DirectionInterface $direction, mixed $value, #[Language('PHP')] string $type): mixed
    {
        $context = $this->createContext($value, $direction);

        $instance = $context->getTypeByStatement(
            statement: $this->parser->getStatementByDefinition($type),
        );

        return $instance->cast($value, $context);
    }

    /**
     * Returns {@see true} if the value can be mapped for the given type.
     *
     * @param non-empty-string $type
     *
     * @throws DefinitionException in case of type building exception occurs
     * @throws \Throwable in case of any internal error occurs
     */
    public function canMap(DirectionInterface $direction, mixed $value, #[Language('PHP')] string $type): bool
    {
        $context = $this->createContext($value, $direction);

        $instance = $context->getTypeByStatement(
            statement: $this->parser->getStatementByDefinition($type),
        );

        return $instance->match($value, $context);
    }

    public function getDefinitionByValue(mixed $value): string
    {
        return $this->extractor->getDefinitionByValue($value);
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
    public function getType(DirectionInterface $direction, #[Language('PHP')] string $type): TypeInterface
    {
        $repository = $this->getTypeRepository($direction);

        return $repository->getTypeByStatement(
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
    public function getTypeByValue(DirectionInterface $direction, mixed $value): TypeInterface
    {
        return $this->getType(
            direction: $direction,
            type: $this->extractor->getDefinitionByValue($value),
        );
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
    public function warmup(DirectionInterface $direction, string|object $class): void
    {
        if (\is_object($class)) {
            $class = $class::class;
        }

        $this->getType($direction, $class);
    }
}
