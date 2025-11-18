<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Context\Path\Path;
use TypeLang\Mapper\Context\Path\PathInterface;
use TypeLang\Mapper\Kernel\Repository\TypeRepositoryInterface;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper
 */
final class RootContext extends RuntimeContext
{
    public static function createFromMapperContext(
        MapperContext $context,
        mixed $value,
        DirectionInterface $direction,
        TypeRepositoryInterface $types,
    ): self {
        $config = $context->config;

        if (!$config->isStrictTypesOptionDefined()) {
            $config = $config->withStrictTypes($direction->isOutput());
        }

        return new self(
            value: $value,
            direction: $direction,
            types: $types,
            parser: $context->parser,
            extractor: $context->extractor,
            platform: $context->platform,
            config: $config,
        );
    }

    public function getPath(): PathInterface
    {
        return Path::empty();
    }

    public function getIterator(): \Traversable
    {
        yield $this;
    }
}
