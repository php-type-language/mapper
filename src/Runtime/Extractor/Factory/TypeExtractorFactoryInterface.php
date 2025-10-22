<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Extractor\Factory;

use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Runtime\Extractor\TypeExtractorInterface;

/**
 * Responsible for the initialization logic of the {@see TypeExtractorInterface}.
 *
 * ```
 * $extractor = $factory->createTypeExtractor($config);
 * ```
 */
interface TypeExtractorFactoryInterface
{
    public function createTypeExtractor(Configuration $config): TypeExtractorInterface;
}
