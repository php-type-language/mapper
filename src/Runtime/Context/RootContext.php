<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Context;

use TypeLang\Mapper\Runtime\ConfigurationInterface;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Repository\TypeRepository;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper
 */
final class RootContext extends Context
{
    public static function forNormalization(ConfigurationInterface $config, TypeRepository $types): self
    {
        return new self(
            direction: Direction::Normalize,
            types: $types,
            config: $config,
        );
    }

    public static function forDenormalization(ConfigurationInterface $config, TypeRepository $types): self
    {
        return new self(
            direction: Direction::Denormalize,
            types: $types,
            config: $config,
        );
    }
}
