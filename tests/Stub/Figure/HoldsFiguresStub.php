<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Figure;

/**
 * Properties typed by the classes of several levels of the hierarchy.
 */
final class HoldsFiguresStub
{
    public FigureStub $figure;

    public PolygonFigureStub $polygon;

    public SquareFigureStub $square;

    public ?QuadFigureStub $quad = null;
}
