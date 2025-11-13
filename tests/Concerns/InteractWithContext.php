<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Concerns;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\Direction;
use TypeLang\Mapper\Context\RootMappingContext;

trait InteractWithContext
{
    use InteractWithTypeParser;
    use InteractWithTypeExtractor;
    use InteractWithConfiguration;
    use InteractWithTypeRepository;

    protected static function createNormalizationContext(mixed $value, ?Configuration $config = null): RootMappingContext
    {
        return RootMappingContext::create(
            value: $value,
            direction: Direction::Normalize,
            config: $config ?? self::getConfiguration(),
            extractor: self::getTypeExtractor(),
            parser: self::getTypeParser(),
            types: self::getTypeRepository(Direction::Normalize),
        );
    }

    protected static function createDenormalizationContext(mixed $value, ?Configuration $config = null): RootMappingContext
    {
        return RootMappingContext::create(
            value: $value,
            direction: Direction::Denormalize,
            config: $config ?? self::getConfiguration(),
            extractor: self::getTypeExtractor(),
            parser: self::getTypeParser(),
            types: self::getTypeRepository(Direction::Denormalize),
        );
    }
}
