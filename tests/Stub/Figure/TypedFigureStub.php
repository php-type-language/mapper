<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Figure;

/**
 * Class of the map with its own property named as the tag field.
 */
final class TypedFigureStub extends FigureStub
{
    public string $type = 'own';
}
