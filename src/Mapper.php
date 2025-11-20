<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Context\BootContext;
use TypeLang\Mapper\Context\Direction;
use TypeLang\Mapper\Context\DirectionInterface;
use TypeLang\Mapper\Context\MapperContext;
use TypeLang\Mapper\Context\RootContext;
use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Definition\DefinitionException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Runtime\RuntimeException;
use TypeLang\Mapper\Kernel\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Kernel\Parser\TypeParserInterface;
use TypeLang\Mapper\Kernel\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Type\TypeInterface;

final class Mapper implements NormalizerInterface, DenormalizerInterface
{
    private readonly TypeRepositoryInterface $types;

    public readonly MapperContext $context;

    public function __construct(
        private readonly PlatformInterface $platform = new StandardPlatform(),
        private readonly Configuration $config = new Configuration(),
    ) {
        $this->context = $this->createMapperContext(
            context: $this->createBootContext(),
        );

        $this->types = $this->createTypeRepository(
            context: $this->context,
        );
    }

    private function createBootContext(): BootContext
    {
        return BootContext::create(
            platform: $this->platform,
            config: $this->config,
        );
    }

    private function createMapperContext(BootContext $context): MapperContext
    {
        return MapperContext::createFromBootContext(
            context: $context,
            extractor: $this->createTypeExtractor($context),
            parser: $this->createTypeParser($context),
        );
    }

    private function createTypeExtractor(BootContext $context): TypeExtractorInterface
    {
        $factory = $context->config->getTypeExtractorFactory();

        return $factory->createTypeExtractor($context);
    }

    private function createTypeParser(BootContext $context): TypeParserInterface
    {
        $factory = $context->config->getTypeParserFactory();

        return $factory->createTypeParser($context);
    }

    private function createTypeRepository(MapperContext $context): TypeRepositoryInterface
    {
        $factory = $context->config->getTypeRepositoryFactory();

        return $factory->createTypeRepository($context);
    }

    private function createRuntimeContext(mixed $value, DirectionInterface $direction): RuntimeContext
    {
        return RootContext::createFromMapperContext(
            context: $this->context,
            value: $value,
            direction: $direction,
            types: $this->types,
        );
    }

    public function normalize(mixed $value, #[Language('PHP')] ?string $type = null): mixed
    {
        $type ??= $this->context->getDefinitionByValue($value);

        return $this->map(Direction::Normalize, $value, $type);
    }

    public function isNormalizable(mixed $value, #[Language('PHP')] ?string $type = null): bool
    {
        $type ??= $this->context->getDefinitionByValue($value);

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
        $context = $this->createRuntimeContext($value, $direction);

        $instance = $context->getTypeByStatement(
            statement: $this->context->parser->getStatementByDefinition($type),
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
        $context = $this->createRuntimeContext($value, $direction);

        $instance = $context->getTypeByStatement(
            statement: $this->context->getStatementByDefinition($type),
        );

        return $instance->match($value, $context) !== null;
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
            statement: $this->context->getStatementByDefinition($type),
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
        $definition = $this->context->getDefinitionByValue($value);

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
