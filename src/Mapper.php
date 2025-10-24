<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Context\Direction;
use TypeLang\Mapper\Context\RootContext;
use TypeLang\Mapper\Runtime\Extractor\Factory\DefaultTypeExtractorFactory;
use TypeLang\Mapper\Runtime\Extractor\Factory\TypeExtractorFactoryInterface;
use TypeLang\Mapper\Runtime\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Runtime\Parser\Factory\DefaultTypeParserFactory;
use TypeLang\Mapper\Runtime\Parser\Factory\TypeParserFactoryInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\Factory\DefaultTypeRepositoryFactory;
use TypeLang\Mapper\Runtime\Repository\Factory\TypeRepositoryFactoryInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;

final class Mapper implements NormalizerInterface, DenormalizerInterface
{
    private readonly TypeExtractorInterface $extractor;

    private readonly TypeParserInterface $parser;

    private readonly TypeRepositoryInterface $normalize;

    private readonly TypeRepositoryInterface $denormalize;

    public function __construct(
        PlatformInterface $platform = new StandardPlatform(),
        private readonly Configuration $config = new Configuration(),
        TypeExtractorFactoryInterface $extractorFactory = new DefaultTypeExtractorFactory(),
        TypeParserFactoryInterface $typeParserFactory = new DefaultTypeParserFactory(),
        TypeRepositoryFactoryInterface $typeRepositoryFactory = new DefaultTypeRepositoryFactory(),
    ) {
        $this->extractor = $extractorFactory->createTypeExtractor($config);
        $this->parser = $typeParserFactory->createTypeParser($config, $platform);

        $this->normalize = $typeRepositoryFactory->createTypeRepository(
            config: $config,
            platform: $platform,
            parser: $this->parser,
            direction: Direction::Normalize,
        );

        $this->denormalize = $typeRepositoryFactory->createTypeRepository(
            config: $config,
            platform: $platform,
            parser: $this->parser,
            direction: Direction::Denormalize,
        );
    }

    public function normalize(mixed $value, #[Language('PHP')] ?string $type = null): mixed
    {
        $type ??= $this->extractor->getDefinitionByValue($value);

        $instance = $this->normalize->getTypeByStatement(
            statement: $this->parser->getStatementByDefinition($type),
        );

        return $instance->cast($value, RootContext::forNormalization(
            value: $value,
            config: $this->config,
            extractor: $this->extractor,
            parser: $this->parser,
            types: $this->normalize,
        ));
    }

    public function isNormalizable(mixed $value, #[Language('PHP')] ?string $type = null): bool
    {
        $type ??= $this->extractor->getDefinitionByValue($value);

        $instance = $this->normalize->getTypeByStatement(
            statement: $this->parser->getStatementByDefinition($type),
        );

        return $instance->match($value, RootContext::forNormalization(
            value: $value,
            config: $this->config,
            extractor: $this->extractor,
            parser: $this->parser,
            types: $this->normalize,
        ));
    }

    public function denormalize(mixed $value, #[Language('PHP')] string $type): mixed
    {
        $instance = $this->denormalize->getTypeByStatement(
            statement: $this->parser->getStatementByDefinition($type),
        );

        return $instance->cast($value, RootContext::forDenormalization(
            value: $value,
            config: $this->config,
            extractor: $this->extractor,
            parser: $this->parser,
            types: $this->denormalize,
        ));
    }

    public function isDenormalizable(mixed $value, #[Language('PHP')] string $type): bool
    {
        $instance = $this->denormalize->getTypeByStatement(
            statement: $this->parser->getStatementByDefinition($type),
        );

        return $instance->match($value, RootContext::forDenormalization(
            value: $value,
            config: $this->config,
            extractor: $this->extractor,
            parser: $this->parser,
            types: $this->denormalize,
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
    public function getType(#[Language('PHP')] string $type, Direction $direction): TypeInterface
    {
        if ($direction === Direction::Normalize) {
            return $this->normalize->getTypeByStatement(
                statement: $this->parser->getStatementByDefinition($type),
            );
        }

        return $this->denormalize->getTypeByStatement(
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
    public function getTypeByValue(mixed $value, Direction $direction): TypeInterface
    {
        return $this->getType(
            type: $this->extractor->getDefinitionByValue($value),
            direction: $direction,
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
    public function warmup(string|object $class): void
    {
        if (\is_object($class)) {
            $class = $class::class;
        }

        foreach (Direction::cases() as $direction) {
            $this->getType($class, $direction);
        }
    }
}
