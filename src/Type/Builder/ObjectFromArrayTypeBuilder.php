<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\ObjectType\ObjectTypeDenormalizer;
use TypeLang\Mapper\Type\TypeInterface;

class ObjectFromArrayTypeBuilder extends ObjectTypeBuilder
{
    protected function create(): TypeInterface
    {
        return new ObjectTypeDenormalizer();
    }
}
