<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\Common\InteractWithTypeRepository;
use TypeLang\Mapper\Kernel\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Kernel\Parser\TypeParserInterface;
use TypeLang\Mapper\Kernel\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Platform\PlatformInterface;

class BuildingContext extends MapperContext implements
    TypeRepositoryInterface
{
    use InteractWithTypeRepository;

    protected function __construct(
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
        TypeExtractorInterface $extractor,
        PlatformInterface $platform,
        Configuration $config,
    ) {
        parent::__construct(
            parser: $parser,
            extractor: $extractor,
            platform: $platform,
            config: $config,
        );

        $this->types = $types;
    }

    public static function createFromMapperContext(
        MapperContext $context,
        TypeRepositoryInterface $types,
    ): self {
        return new self(
            types: $types,
            parser: $context->parser,
            extractor: $context->extractor,
            platform: $context->platform,
            config: $context->config,
        );
    }
}
