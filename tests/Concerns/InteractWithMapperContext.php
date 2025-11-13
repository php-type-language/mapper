<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Concerns;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\MapperContext;

trait InteractWithMapperContext
{
    use InteractWithTypeParser;
    use InteractWithTypeExtractor;
    use InteractWithConfiguration;

    protected static function createMapperContext(?Configuration $config = null): MapperContext
    {
        return MapperContext::create(
            config: $config ?? self::getConfiguration(),
            extractor: self::getTypeExtractor(),
            parser: self::getTypeParser(),
        );
    }
}
