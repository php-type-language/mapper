<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Concerns;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\MapperContext;

trait InteractWithMapperContext
{
    use InteractWithBootContext;
    use InteractWithTypeParser;
    use InteractWithTypeExtractor;

    protected static function createMapperContext(?Configuration $config = null): MapperContext
    {
        $context = self::createBootContext($config);

        return MapperContext::createFromBootContext(
            context: $context,
            extractor: self::getTypeExtractor(),
            parser: self::getTypeParser(),
        );
    }
}
