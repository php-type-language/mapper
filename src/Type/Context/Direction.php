<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Context;

enum Direction
{
    case Normalize;
    case Denormalize;
}
