<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\Common\InteractWithTypeParser;
use TypeLang\Mapper\Kernel\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Kernel\Parser\TypeParserInterface;
use TypeLang\Mapper\Platform\PlatformInterface;

/**
 * Contains information about a ready-to-use (initialized) mapper
 */
class MapperContext extends Context implements
    TypeExtractorInterface,
    TypeParserInterface
{
    use InteractWithTypeParser;

    protected function __construct(
        TypeParserInterface $parser,
        TypeExtractorInterface $extractor,
        PlatformInterface $platform,
        Configuration $config,
    ) {
        parent::__construct(
            platform: $platform,
            config: $config,
        );

        $this->extractor = $extractor;
        $this->parser = $parser;
    }

    public static function createFromBootContext(
        BootContext $context,
        TypeExtractorInterface $extractor,
        TypeParserInterface $parser,
    ): self {
        return new self(
            parser: $parser,
            extractor: $extractor,
            platform: $context->platform,
            config: $context->config,
        );
    }

    /**
     * A more convenient and correct way to get current "object as array"
     * configuration value.
     *
     * @see Configuration::isObjectAsArray()
     *
     * @link https://en.wikipedia.org/wiki/Law_of_Demeter
     */
    public function isObjectAsArray(): bool
    {
        return $this->config->isObjectAsArray();
    }

    /**
     * A more convenient and correct way to get current "strict types"
     * configuration value.
     *
     * @see Configuration::isStrictTypesEnabled()
     *
     * @link https://en.wikipedia.org/wiki/Law_of_Demeter
     */
    public function isStrictTypesEnabled(): bool
    {
        return $this->config->isStrictTypesEnabled();
    }
}
