<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Parser\Factory;

use TypeLang\Mapper\Context\BootContext;
use TypeLang\Mapper\Kernel\Parser\TypeParserInterface;

/**
 * Responsible for the initialization logic of the {@see TypeParserInterface}.
 *
 * ```
 * $parser = $factory->createTypeParser($config, new EmptyPlatform());
 * ```
 */
interface TypeParserFactoryInterface
{
    public function createTypeParser(BootContext $context): TypeParserInterface;
}
