<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\ObjectToArrayType;
use TypeLang\Mapper\Type\TypeInterface;

class ObjectToArrayTypeBuilder extends ObjectTypeBuilder
{
    protected function create(): TypeInterface
    {
        return new ObjectToArrayType();
    }
}
