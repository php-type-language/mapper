<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\ListType\ListFromArrayType;
use TypeLang\Mapper\Type\TypeInterface;

class ListFromArrayTypeBuilder extends ListTypeBuilder
{
    protected function create(TypeInterface $type): ListFromArrayType
    {
        return new ListFromArrayType($type);
    }
}
