<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

class ArrayKeyType extends UnionType
{
    public function __construct()
    {
        parent::__construct([new IntType(), new StringType()]);
    }
}
