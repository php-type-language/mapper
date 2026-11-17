<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Shape;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

/**
 * Polymorphic base, `SquareStub` is written relative to the namespace.
 */
#[DiscriminatorMap(field: 'type', map: [
    'circle' => CircleStub::class,
    'square' => 'SquareStub',
    'group' => GroupStub::class,
])]
abstract class ShapeStub {}
