<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Concerns;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\Direction;
use TypeLang\Mapper\Context\RootRuntimeContext;

trait InteractWithRuntimeContext
{
    use InteractWithMapperContext;
    use InteractWithTypeRepository;

    protected static function createNormalizationContext(mixed $value, ?Configuration $config = null): RootRuntimeContext
    {
        return RootRuntimeContext::createFromMapperContext(
            context: self::createMapperContext($config),
            value: $value,
            direction: Direction::Normalize,
            types: self::getTypeRepository(Direction::Normalize),
        );
    }

    protected static function createDenormalizationContext(mixed $value, ?Configuration $config = null): RootRuntimeContext
    {
        return RootRuntimeContext::createFromMapperContext(
            context: self::createMapperContext($config),
            value: $value,
            direction: Direction::Denormalize,
            types: self::getTypeRepository(Direction::Denormalize),
        );
    }
}
