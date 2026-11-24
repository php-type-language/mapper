<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\Struct;

use TypeLang\Mapper\Mapping\Metadata\Field\OutputFieldMetadata;

/**
 * Structure of the data a class is normalized to.
 */
final class OutputStructMetadata
{
    public function __construct(
        /**
         * In order of the keys of the data
         *
         * @var list<OutputFieldMetadata>
         */
        public readonly array $fields = [],
        /**
         * Whether an object is normalized to an array or to a
         * {@see \stdClass}, {@see null} in case of the configuration of the
         * mapper
         */
        public readonly ?bool $normalizeAsArray = null,
    ) {}
}
