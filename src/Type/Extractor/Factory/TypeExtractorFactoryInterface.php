<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Extractor\Factory;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Type\Extractor\TypeExtractorInterface;

/**
 * Responsible for the initialization logic of the {@see TypeExtractorInterface}.
 *
 * ```
 * $extractor = $factory->createTypeExtractor($config);
 * ```
 */
interface TypeExtractorFactoryInterface
{
    public function createTypeExtractor(Configuration $config, PlatformInterface $platform): TypeExtractorInterface;
}
