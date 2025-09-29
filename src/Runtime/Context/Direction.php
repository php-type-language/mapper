<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Context;

enum Direction
{
    case Normalize;
    case Denormalize;
}
