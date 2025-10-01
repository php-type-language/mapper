<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver;

use TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

/**
 * @phpstan-import-type PropertyConfigType from ArrayConfigDriver
 */
interface PropertyConfigLoaderInterface
{
    /**
     * @param PropertyConfigType $config
     */
    public function load(
        array $config,
        \ReflectionProperty $property,
        PropertyMetadata $metadata,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void;
}
