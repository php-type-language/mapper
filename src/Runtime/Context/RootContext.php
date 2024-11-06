<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Context;

use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Runtime\ConfigurationInterface;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Parser\TypeParserFacadeInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryFacadeInterface;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper
 */
final class RootContext extends Context
{
    public static function forNormalization(
        mixed $value,
        ConfigurationInterface $config,
        TypeParserFacadeInterface $parser,
        TypeRepositoryFacadeInterface $types,
    ): self {
        if ($config instanceof Configuration) {
            // Disable strict-types for denormalization if option is not set
            if (!$config->isStrictTypesOptionDefined()) {
                $config = $config->withStrictTypes(false);
            }

            // ...
        }

        return new self(
            value: $value,
            direction: Direction::Normalize,
            types: $types,
            parser: $parser,
            config: $config,
        );
    }

    public static function forDenormalization(
        mixed $value,
        ConfigurationInterface $config,
        TypeParserFacadeInterface $parser,
        TypeRepositoryFacadeInterface $types,
    ): self {
        if ($config instanceof Configuration) {
            // Enable strict-types for normalization if option is not set
            if (!$config->isStrictTypesOptionDefined()) {
                $config = $config->withStrictTypes(true);
            }

            // ...
        }

        return new self(
            value: $value,
            direction: Direction::Denormalize,
            types: $types,
            parser: $parser,
            config: $config,
        );
    }
}
