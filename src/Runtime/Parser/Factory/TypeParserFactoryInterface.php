<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Parser\Factory;

use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;

/**
 * Responsible for the initialization logic of the {@see TypeParserInterface}.
 *
 * ```
 * $parser = $factory->createTypeParser($config, new EmptyPlatform());
 * ```
 */
interface TypeParserFactoryInterface
{
    public function createTypeParser(Configuration $config, PlatformInterface $platform): TypeParserInterface;
}
