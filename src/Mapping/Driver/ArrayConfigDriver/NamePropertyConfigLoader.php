<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

final class NamePropertyConfigLoader extends PropertyConfigLoader
{
    public function load(
        array $config,
        \ReflectionProperty $property,
        PropertyMetadata $metadata,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        if (!\array_key_exists('name', $config)) {
            return;
        }

        // @phpstan-ignore-next-line : Additional DbC invariant
        assert(\is_string($config['name']));

        $metadata->alias = $config['name'];
    }
}
