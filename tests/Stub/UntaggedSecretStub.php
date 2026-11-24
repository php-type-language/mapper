<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

final class UntaggedSecretStub extends TaggedSecretStub
{
    public int $extra = 1;
}
