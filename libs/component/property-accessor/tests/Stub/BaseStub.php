<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Tests\Stub;

/**
 * Parent class in the inheritance-scope fixtures.
 */
class BaseStub
{
    public string $basePublic = 'base-public';

    protected string $baseProtected = 'base-protected';

    private string $basePrivate = 'base-private';

    public function getBasePrivate(): string
    {
        return $this->basePrivate;
    }
}
