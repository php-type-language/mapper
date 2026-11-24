<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Figure;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

/**
 * Root of a map with classes at several levels of the hierarchy.
 */
#[DiscriminatorMap(field: 'type', map: [
    'square' => SquareFigureStub::class,
    'quad' => QuadFigureStub::class,
    'rect' => RectFigureStub::class,
    'circle' => CircleFigureStub::class,
    1 => NumberFigureStub::class,
    'typed' => TypedFigureStub::class,
])]
abstract class FigureStub {}
