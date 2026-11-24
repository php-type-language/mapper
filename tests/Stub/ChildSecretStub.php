<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

final class ChildSecretStub extends ParentSecretStub
{
    public int $own = 1;
}
