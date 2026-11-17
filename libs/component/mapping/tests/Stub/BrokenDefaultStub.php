<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

final class BrokenDefaultStub
{
    // @phpstan-ignore-next-line the constant is missing on purpose
    public int $value = self::MISSING;
}
