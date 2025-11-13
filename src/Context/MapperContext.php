<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\Common\InteractWithTypeExtractor;
use TypeLang\Mapper\Context\Common\InteractWithTypeParser;
use TypeLang\Mapper\Type\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;

class MapperContext implements
    TypeExtractorInterface,
    TypeParserInterface
{
    use InteractWithTypeExtractor;
    use InteractWithTypeParser;

    protected function __construct(
        TypeParserInterface $parser,
        TypeExtractorInterface $extractor,
        /**
         * Gets current configuration.
         *
         * If you need to retrieve configuration's settings, it is recommended
         * to use the following methods:
         *
         * - {@see RuntimeContext::isObjectAsArray()}
         * - {@see RuntimeContext::isStrictTypesEnabled()}
         */
        public readonly Configuration $config,
    ) {
        $this->extractor = $extractor;
        $this->parser = $parser;
    }

    public static function create(
        Configuration $config,
        TypeExtractorInterface $extractor,
        TypeParserInterface $parser,
    ): self {
        return new self(
            parser: $parser,
            extractor: $extractor,
            config: $config,
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
