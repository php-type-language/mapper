<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\Common\InteractWithTypeRepository;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Type\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;

class BuildingContext extends MapperContext implements
    TypeRepositoryInterface
{
    use InteractWithTypeRepository;

    protected function __construct(
        /**
         * Gets data transformation direction.
         */
        public readonly DirectionInterface $direction,
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
        DirectionInterface $direction,
        TypeRepositoryInterface $types,
    ): self {
        return new self(
            direction: $direction,
            types: $types,
            parser: $context->parser,
            extractor: $context->extractor,
            platform: $context->platform,
            config: $context->config,
        );
    }
}
