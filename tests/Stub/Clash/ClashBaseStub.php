<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Clash;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

/**
 * Nested maps on the same field: the square gets the tag of the polygon here.
 */
#[DiscriminatorMap(field: 'type', map: ['polygon' => ClashPolygonStub::class])]
abstract class ClashBaseStub {}
