<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\Path\Path;
use TypeLang\Mapper\Context\Path\PathInterface;
use TypeLang\Mapper\Type\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper
 */
final class RootMappingContext extends MappingContext
{
    private PathInterface $path;

    public static function create(
        mixed $value,
        DirectionInterface $direction,
        Configuration $config,
        TypeExtractorInterface $extractor,
        TypeParserInterface $parser,
        TypeRepositoryInterface $types,
    ): self {
        if (!$config->isStrictTypesOptionDefined()) {
            $config = $config->withStrictTypes(
                enabled: $direction->isSafeTypes(),
            );
        }

        return new self(
            value: $value,
            direction: $direction,
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

    public function getIterator(): \Traversable
    {
        yield $this;
    }
}
