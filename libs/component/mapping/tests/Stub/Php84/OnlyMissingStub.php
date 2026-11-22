<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Php84;

use TypeLang\Mapper\Mapping\Direction;
use TypeLang\Mapper\Mapping\MapOnly;

final class OnlyMissingStub
{
    #[MapOnly(Direction::Denormalization)]
    public string $computed {
        get => 'computed';
    }
}
