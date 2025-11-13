<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Extractor\Factory;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Type\Extractor\NativeTypeExtractor;

/**
 * Implements the basic (built-in) logic for initializing the
 * {@see NativeTypeExtractor} instance.
 */
final class DefaultTypeExtractorFactory implements TypeExtractorFactoryInterface
{
    public function createTypeExtractor(Configuration $config, PlatformInterface $platform): NativeTypeExtractor
    {
        return new NativeTypeExtractor();
    }
}
