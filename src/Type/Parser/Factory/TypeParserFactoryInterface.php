<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Parser\Factory;

use TypeLang\Mapper\Context\BootContext;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;

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
