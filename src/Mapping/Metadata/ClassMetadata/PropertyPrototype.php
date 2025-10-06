<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata\DefaultValuePrototype;
use TypeLang\Mapper\Mapping\Metadata\ConditionPrototypeSet;
use TypeLang\Mapper\Mapping\Metadata\TypePrototype;

final class PropertyPrototype
{
    /**
     * @var non-empty-string
     */
    public string $alias;

    public TypePrototype $read;

    public TypePrototype $write;

    public ?DefaultValuePrototype $default = null;

    public readonly ConditionPrototypeSet $skip;

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
    ) {
        $this->alias = $name;
        $this->skip = new ConditionPrototypeSet();
        $this->read = $this->write = new TypePrototype('mixed');
    }
}
