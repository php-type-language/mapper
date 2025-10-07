<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Context;

use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Runtime\ConfigurationInterface;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Path\Path;
use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper
 */
final class RootContext extends Context
{
    private PathInterface $path;

    public static function forNormalization(
        mixed $value,
        ConfigurationInterface $config,
        TypeExtractorInterface $extractor,
        TypeParserInterface $parser,
        TypeRepositoryInterface $types,
    ): self {
        if ($config instanceof Configuration) {
            // Disable strict-types for normalization if option is not set
            if (!$config->isStrictTypesOptionDefined()) {
                $config = $config->withStrictTypes(false);
            }

            // ...
        }

        return new self(
            value: $value,
            direction: Direction::Normalize,
            extractor: $extractor,
            parser: $parser,
            types: $types,
            config: $config,
        );
    }

    public static function forDenormalization(
        mixed $value,
        ConfigurationInterface $config,
        TypeExtractorInterface $extractor,
        TypeParserInterface $parser,
        TypeRepositoryInterface $types,
    ): self {
        if ($config instanceof Configuration) {
            // Enable strict-types for denormalization if option is not set
            if (!$config->isStrictTypesOptionDefined()) {
                $config = $config->withStrictTypes(true);
            }

            // ...
        }

        return new self(
            value: $value,
            direction: Direction::Denormalize,
            extractor: $extractor,
            parser: $parser,
            types: $types,
            config: $config,
        );
    }

    public function getPath(): PathInterface
    {
        return $this->path ??= new Path();
    }
}
