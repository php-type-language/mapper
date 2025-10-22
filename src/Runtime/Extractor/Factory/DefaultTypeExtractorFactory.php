<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Extractor\Factory;

use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Runtime\Extractor\NativeTypeExtractor;

/**
 * Implements the basic (built-in) logic for initializing the
 * {@see NativeTypeExtractor} instance.
 */
final class DefaultTypeExtractorFactory implements TypeExtractorFactoryInterface
{
    public function createTypeExtractor(Configuration $config): NativeTypeExtractor
    {
        return new NativeTypeExtractor();
    }
}
