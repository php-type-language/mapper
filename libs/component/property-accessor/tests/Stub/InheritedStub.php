<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Tests\Stub;

final class InheritedStub extends BaseStub
{
    public string $childPublic = 'child-public';

    private string $childPrivate = 'child-private';

    public function getChildPrivate(): string
    {
        return $this->childPrivate;
    }
}
