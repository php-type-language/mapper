<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Shape;

final class GroupStub extends ShapeStub
{
    /**
     * @var list<ShapeStub>
     */
    public array $children;
}
