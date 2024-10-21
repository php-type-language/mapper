<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Context;

enum Direction implements DirectionInterface
{
    case Normalize;
    case Denormalize;

    public function isNormalization(): bool
    {
        return $this === self::Normalize;
    }

    public function isDenormalization(): bool
    {
        return $this === self::Denormalize;
    }
}
