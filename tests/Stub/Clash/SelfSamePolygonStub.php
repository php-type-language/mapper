<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Clash;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

/**
 * Maps itself on the field of the map of its parent with the same tag.
 */
#[DiscriminatorMap(field: 'type', map: ['poly' => SelfSamePolygonStub::class])]
class SelfSamePolygonStub extends SelfSameBaseStub
{
    public int $side = 1;
}
