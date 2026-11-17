<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Tests\Stub;

final class AsymmetricStub
{
    public private(set) string $privateSet = 'private-set';

    public protected(set) string $protectedSet = 'protected-set';
}
