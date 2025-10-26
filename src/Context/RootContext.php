<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Context\Path\Path;
use TypeLang\Mapper\Context\Path\PathInterface;
use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Runtime\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
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
        Configuration $config,
        TypeExtractorInterface $extractor,
        TypeParserInterface $parser,
        TypeRepositoryInterface $types,
    ): self {
        // Disable strict-types for normalization if option is not set
        if (!$config->isStrictTypesOptionDefined()) {
            $config = $config->withStrictTypes(false);
        }

        // ...

        return new self(
            value: $value,
            direction: Direction::Normalize,
            config: $config,
            extractor: $extractor,
            parser: $parser,
            types: $types,
        );
    }

    public static function forDenormalization(
        mixed $value,
        Configuration $config,
        TypeExtractorInterface $extractor,
        TypeParserInterface $parser,
        TypeRepositoryInterface $types,
    ): self {
        // Enable strict-types for denormalization if option is not set
        if (!$config->isStrictTypesOptionDefined()) {
            $config = $config->withStrictTypes(true);
        }

        // ...

        return new self(
            value: $value,
            direction: Direction::Denormalize,
            config: $config,
            extractor: $extractor,
            parser: $parser,
            types: $types,
        );
    }

    public function getPath(): PathInterface
    {
        return $this->path ??= new Path();
    }
}
