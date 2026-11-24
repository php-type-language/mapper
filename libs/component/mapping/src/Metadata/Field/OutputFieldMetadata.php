<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\Field;

use TypeLang\Mapper\Mapping\Metadata\Access\AccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\SkipConditionInterface;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;

/**
 * Field of the data a class is normalized to.
 */
final class OutputFieldMetadata extends FieldMetadata
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        string $name,
        AccessMetadata $access,
        ?TypeMetadata $type = null,
        /**
         * Conditions any of which omits the field from the data
         *
         * @var list<SkipConditionInterface>
         */
        public readonly array $skip = [],
    ) {
        parent::__construct($name, $access, $type);
    }
}
