<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping;

use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Runtime\ConfigurationInterface;
use TypeLang\Mapper\Runtime\Context\Direction;
use TypeLang\Mapper\Runtime\Context\RootContext;
use TypeLang\Mapper\Runtime\Extractor\NativeTypeExtractor;
use TypeLang\Mapper\Runtime\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Runtime\Parser\TypeLangParser;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepository;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Tests\TestCase;

abstract class MappingTestCase extends TestCase
{
    protected function createConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }

    protected function createTypeExtractor(): TypeExtractorInterface
    {
        return new NativeTypeExtractor();
    }

    protected function createPlatform(): PlatformInterface
    {
        return new StandardPlatform();
    }

    protected function createTypeParser(): TypeParserInterface
    {
        return TypeLangParser::createFromPlatform($this->createPlatform());
    }

    protected function createTypeRepository(Direction $direction): TypeRepositoryInterface
    {
        $platform = $this->createPlatform();

        return new TypeRepository(
            parser: $this->createTypeParser(),
            builders: $platform->getTypes($direction),
        );
    }

    protected function createDenormalizationContext(mixed $value): RootContext
    {
        return RootContext::forDenormalization(
            value: $value,
            config: $this->createConfiguration(),
            extractor: $this->createTypeExtractor(),
            parser: $this->createTypeParser(),
            types: $this->createTypeRepository(Direction::Denormalize),
        );
    }

    protected function createNormalizationContext(mixed $value): RootContext
    {
        return RootContext::forNormalization(
            value: $value,
            config: $this->createConfiguration(),
            extractor: $this->createTypeExtractor(),
            parser: $this->createTypeParser(),
            types: $this->createTypeRepository(Direction::Normalize),
        );
    }
}
