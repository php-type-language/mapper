<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

final class NamingCollisionStub
{
    public int $userId = 0;

    public int $user_id = 0;
}
