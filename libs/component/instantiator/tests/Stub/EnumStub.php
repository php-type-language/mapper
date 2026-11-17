<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Instantiator\Tests\Stub;

enum EnumStub
{
    case Active;
    case Inactive;

    public static function fromLabel(string $label): self
    {
        return match ($label) {
            'on' => self::Active,
            default => self::Inactive,
        };
    }
}
