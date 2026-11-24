<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Clash;

final class ClashBothStub implements ClashLeftStub, ClashRightStub
{
    public int $value = 1;
}
