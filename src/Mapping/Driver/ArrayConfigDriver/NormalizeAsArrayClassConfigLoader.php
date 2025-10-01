<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver\AttributeDriver;

use TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver\ClassConfigLoader;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

final class NormalizeAsArrayClassConfigLoader extends ClassConfigLoader
{
    public function load(
        array $config,
        \ReflectionClass $class,
        ClassMetadata $metadata,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        if (!\array_key_exists('normalize_as_array', $config)) {
            return;
        }

        // @phpstan-ignore-next-line : Additional DbC invariant
        assert(\is_bool($config['normalize_as_array']));

        $metadata->isNormalizeAsArray = $config['normalize_as_array'];
    }
}
