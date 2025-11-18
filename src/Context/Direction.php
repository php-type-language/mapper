<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

enum Direction: string implements DirectionInterface
{
    case Normalize = 'normalize';
    case Denormalize = 'denormalize';

    public function getName(): string
    {
        return $this->value;
    }

    public function isOutput(): bool
    {
        return $this === self::Normalize;
    }
}
