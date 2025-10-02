<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Info\ClassInfo;

use TypeLang\Mapper\Mapping\Info\ClassInfo\PropertyInfo\DefaultValueInfo;
use TypeLang\Mapper\Mapping\Info\ClassInfo\PropertyInfo\SkipConditionsInfoList;
use TypeLang\Mapper\Mapping\Info\TypeInfo;

final class PropertyInfo
{
    public ?DefaultValueInfo $default = null;

    public ?TypeInfo $read = null;

    public ?TypeInfo $write = null;

    public readonly SkipConditionsInfoList $skip;

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
        $this->skip = new SkipConditionsInfoList();
    }
}
