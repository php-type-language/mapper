<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata\DefaultValueInfo;
use TypeLang\Mapper\Mapping\Metadata\ConditionInfo;
use TypeLang\Mapper\Mapping\Metadata\MetadataInfo;
use TypeLang\Mapper\Mapping\Metadata\ParsedTypeInfo;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeInfo;

final class PropertyInfo extends MetadataInfo
{
    /**
     * @var non-empty-string
     */
    public string $alias;

    public TypeInfo $read;

    public TypeInfo $write;

    public ?DefaultValueInfo $default = null;

    /**
     * @var list<ConditionInfo>
     */
    public array $skip = [];

    /**
     * @var non-empty-string|null
     */
    public ?string $typeErrorMessage = null;

    /**
     * @var non-empty-string|null
     */
    public ?string $undefinedErrorMessage = null;

    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $name,
        ?SourceInfo $source = null,
    ) {
        $this->alias = $name;
        $this->read = $this->write = ParsedTypeInfo::mixed();

        parent::__construct($source);
    }
}
