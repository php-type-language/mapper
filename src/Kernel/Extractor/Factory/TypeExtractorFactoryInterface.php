<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Extractor\Factory;

use TypeLang\Mapper\Context\BootContext;
use TypeLang\Mapper\Kernel\Extractor\TypeExtractorInterface;

/**
 * Responsible for the initialization logic of the {@see TypeExtractorInterface}.
 *
 * ```
 * $extractor = $factory->createTypeExtractor($config);
 * ```
 */
interface TypeExtractorFactoryInterface
{
    public function createTypeExtractor(BootContext $context): TypeExtractorInterface;
}
