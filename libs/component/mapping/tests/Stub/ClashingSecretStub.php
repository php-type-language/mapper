<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

/**
 * Public property with the name of the field of the private property of the
 * parent.
 */
final class ClashingSecretStub extends ParentSecretStub
{
    public string $secret = 'child';
}
