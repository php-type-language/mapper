<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Concerns;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\Direction;
use TypeLang\Mapper\Context\RootContext;

trait InteractWithRuntimeContext
{
    use InteractWithMapperContext;
    use InteractWithTypeRepository;

    protected static function createNormalizationContext(mixed $value, ?Configuration $config = null): RootContext
    {
        return RootContext::createFromMapperContext(
            context: self::createMapperContext($config),
            value: $value,
            direction: Direction::Normalize,
            types: self::getTypeRepository(Direction::Normalize),
        );
    }

    protected static function createDenormalizationContext(mixed $value, ?Configuration $config = null): RootContext
    {
        return RootContext::createFromMapperContext(
            context: self::createMapperContext($config),
            value: $value,
            direction: Direction::Denormalize,
            types: self::getTypeRepository(Direction::Denormalize),
        );
    }
}
