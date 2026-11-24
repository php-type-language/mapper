<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Clash;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

/**
 * Maps itself on the field of the map of its parent with another tag.
 */
#[DiscriminatorMap(field: 'type', map: ['poly' => SelfPolygonStub::class])]
class SelfPolygonStub extends SelfBaseStub {}
