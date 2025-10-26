<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\ListFromIterableType;
use TypeLang\Mapper\Type\TypeInterface;

class ListFromIterableTypeBuilder extends ListTypeBuilder
{
    protected function create(TypeInterface $type): ListFromIterableType
    {
        return new ListFromIterableType($type);
    }
}
