<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Shape;

/**
 * Maps the field of the tag itself.
 */
final class SquareStub extends ShapeStub
{
    public string $type = 'square';

    public int $side;
}
