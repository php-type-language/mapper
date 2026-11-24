<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Figure;

/**
 * Class outside the map with classes of the map among its children.
 */
class PolygonFigureStub extends FigureStub
{
    public int $corners = 4;
}
